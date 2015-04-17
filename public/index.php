<?php
	/** BRM Manager
	 * App for getting approval from multiple users and managing multiple campaigns.
	 *
	 * Written by: Duane Jeffers <djef@tjc.edu>
	 **/

	require_once('../vendor/autoload.php');

	session_cache_limiter(false);
	session_start();

	defined('APPLICATION_ENV') or define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? 
                                  							 getenv('APPLICATION_ENV') : 
                                  							 'production'));

	use \League\Fractal\Manager AS FractalManager;
	use \League\Fractal\Resource\Collection as FractalCollection;
	use \BRMManager\Permissions as Permissions;
	use \Monolog\Logger;
	use \Monolog\Handler\ChromePHPHandler;
	use \Monolog\Handler\LogglyHandler;
	use \Monolog\Formatter\LogglyFormatter;

	$app = new \Slim\Slim(array(
		'mode' => APPLICATION_ENV,
		'view' => new \TJC\View\Layout(),
		'templates.path' => '../app/templates',
	));

	$app->container->singleton('logger', function() {
		return new Logger('Logger');
	});

	$app->configureMode('development', function() use($app) {
		$app->config(array(
			'whoops.editor' => 'sublime',
			'debug' => true
		));

		$app->add(new \Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware);
		
		$app->logger->pushHandler(new ChromePHPHandler());

		\ORM::configure('sqlite:../data/database.db');
		\ORM::configure('logging', true);
		\ORM::configure('logger', function($log_string, $query_time) use($app) {
    		$app->logger->addInfo($log_string . ' TIME: ' . $query_time);
		});
	});

	$app->configureMode('production', function() use($app) {
		\ORM::configure('sqlite:../data/database.db');
	});

	\Model::$auto_prefix_models = '\\BRMManager\\Model\\';

	$app->add(new \BRMManager\Middleware\User);

	$app->view->setLayout('layout/layout.php');

	// This is the default layout files.
 	$app->view->appendJavascriptFile('/components/jquery/dist/jquery.min.js')
 			  ->appendJavascriptFile('/components/moment/min/moment.min.js')
			  ->appendJavascriptFile('/components/bootstrap/dist/js/bootstrap.min.js')
			  ->appendJavascriptFile('/components/jasny-bootstrap/dist/js/jasny-bootstrap.min.js')
			  ->appendJavascriptFile('/components/jquery-ajax-progress/js/jquery.ajax-progress.js')
			  ->appendJavascriptFile('/components/handlebars/handlebars.min.js')
			  ->appendJavascriptFile('/components/typeahead.js/dist/typeahead.bundle.min.js')
			  ->appendJavascriptFile('/components/jqBootstrapValidation/dist/jqBootstrapValidation-1.3.7.min.js')
			  ->appendJavascriptFile('/js/application.js');

	$app->view->appendStylesheet('/components/bootstrap/dist/css/bootstrap.min.css')
			  ->appendStylesheet('/components/jasny-bootstrap/dist/css/jasny-bootstrap.min.css')
			  ->appendStylesheet('/components/fontawesome/css/font-awesome.min.css')
			  ->appendStylesheet('/css/application.css');

	// Injecting Mandrill in to the app.
	$app->container->singleton('mandrill', function() {
		$api_key = include_once('../app/config/mandrill.settings.php');
		return new Mandrill($api_key);
	});

	$checkLogin = function() use($app) {
		// Check the current Session and if the user is logged in.
		if(!$app->user || !$app->user->keepLoginAlive()) {
			$app->flash('info', 'Session Timed Out. Please re-login.');
			$app->redirect("/");
		} else {
			// Pass the User information on in to the system.
			$app->view->setLayoutData('user', $app->user);
		}
	};

	$checkPermissions = function($perm = 'view') use($app) {
		return function() use($perm, $app) {
			if(!$app->user->hasAccess($perm)) {
				$app->flash('danger', 'You do not have the appropriate permissions to use this resource.');
				$app->redirect('/');
			}
		};
	};

	$app->get('/', function() use($app) {
		$app->render('index.php', array());
	});

	$app->get('/logout', function() use($app) {
		unset($_SESSION['user']);
		$app->flash('info', 'User is now logged out.');
		$app->redirect("/");
	});

	$app->group('/login', function() use($app) {
		$app->post('/', function() use($app) {
			if($app->request->isPost()) {
				$email = strtolower($app->request->post('user_email'));
				$user = \ORM::for_table('user')->where('email', $email)->find_one();
	
				if(!$user) {
					$app->flash('error', 'User Does Not Exist');
					$app->redirect('/');
				} else {
					// Create login attempt
					$login_attempt = \ORM::for_table('login_attempts')->create();
					$login_attempt->userid = $user->id;
					$login_attempt->timestamp = time();
	
					$login_attempt->hash = uniqid('user-' . $user->id . '-');
					$login_attempt->save();
	
					$login_url = 'http://' . $_SERVER['HTTP_HOST'] . $app->urlFor('verify-user', array('hash' => $login_attempt->hash));
					$message = include_once('../app/config/loginemail.settings.php');
					$message += array(
						'text' => str_replace(array('%USER%', '%URL%'), array($user->email, $login_url), file_get_contents('../app/templates/email/login.php')),
						'to' => array(array(
							'email' => $user->email,
							'name' => $user->firstname . " " . $user->lastname,
							'type' => 'to'
						))
					);
	
					$result = $app->mandrill->messages->send($message);
	
					$login_attempt->emailid = $result[0]['_id'];
					$login_attempt->save();
	
					$app->flash('info', 'Please Login with the email sent to your email address.');
					$app->redirect('/');
				}
			} else {
				$app->flash('danger', 'You Did Not Submit An User Email.');
				$app->redirect('/');
			}
		});
	
		$app->get('/verify/:hash', function($hash = null) use($app) {
			// Implement DateTime for all time() function calls.
			$curr_time = time();
			$range_time = $curr_time - (20 * 60);
			
			// Create a new class to handle login attempts creation/selection.
			// Class should also handle login requests from users following an update link.
	
			$result = \ORM::for_table('login_attempts')->where('hash', $hash)->where_gt('timestamp', $range_time)->where_lt('timestamp', $curr_time)->find_one();
	
			if(!$result) {
				$app->flash('danger', 'Login Error, Please try again');
				$app->redirect('/');
			} else {
				$user = \ORM::for_table('user')->find_one($result->userid);
	
				$_SESSION['user'] = new \BRMManager\User\Session($user);
	
				$app->redirect('/brm');
			}
		})->name('verify-user');

		$app->get('/brm/:brmid/:hash', function($brmid = NULL, $hash = NULL) use($app) {
			$login_attempt = \Model::factory('LoginAttempts')->where(array('hash' => $hash, 'result' => 0))->find_one();
			// Change the result and then log the user in.
			if(!$login_attempt) {
				$app->flash('danger', 'Login Error: Attempt does not exist.');
				$app->redirect('/');
			} else {
				$login_attempt->result = 1;
				$user = $login_attempt->user();
				$auth = $login_attempt->auth();
				$auth->viewedtime = time();
				$auth->save();
				$login_attempt->save();

				$_SESSION['user'] = new \BRMManager\User\Session($user);

				$app->redirect($app->urlFor('view-brm', array('id' => $auth->brmid)));
			}
		})->name('brm-login');
	});

	$app->group('/brm', $checkLogin, function() use($app) {
		$app->group('/search', function() use($app) {

		});

		$app->get('/', function() use($app) {
			$view = array();

			$app->view->appendJavascriptFile('/components/datatables/media/js/jquery.dataTables.min.js');
			$app->view->appendJavascriptFile('//cdn.datatables.net/plug-ins/1.10.6/integration/bootstrap/3/dataTables.bootstrap.js');
			$app->view->appendJavascriptFile('/js/main.js');
			$app->view->appendStylesheet('//cdn.datatables.net/plug-ins/1.10.6/integration/bootstrap/3/dataTables.bootstrap.css');

			$app->render('brm/index.php', $view);
		});

		$app->map('/list', function() use($app) {
			// Grabbing post data and logging it.
			$app->logger->addInfo(var_export($app->request->post(), true));

			$template_columns = array(
				'id', 'title', 'state', 'launchdate', 'current_version', 'approval_needed', 'approved', 'denied', 'view'
			);

			// Grab the whole list of available table data.
			$list = \Model::factory('View\BRMList');

			// Create jsonArr with preloaded elements.
			$jsonArr = array(
				'draw' => (int) $app->request->post('draw'),
				'recordsTotal' => $list->count(),
			);

			// Do ordering here:
			foreach($app->request->post('order') as $order) {
				switch($order['dir']) {
					case 'asc':
						$list->order_by_asc($template_columns[$order['column']]);
						break;

					case 'desc':
						$list->order_by_desc($template_columns[$order['column']]);
						break;
				}
			}

			// Now do recordsFiltered.
			$jsonArr['recordsFiltered'] = $list->count();

			$list->limit((int) $app->request->post('length'))
				 ->offset((int) $app->request->post('start'));

			$manager = new FractalManager();
			$resource = new FractalCollection($list->find_many(), function($li) use($app, $template_columns) {
				return array(
					$template_columns[0] => $li->id,
					$template_columns[1] => $li->title,
					$template_columns[2] => $li->state,
					$template_columns[3] => (!is_null($li->launchdate) ? date('F j, Y g:i:s', $li->launchdate) : ''),
					$template_columns[4] => $li->brm_current_version,
					$template_columns[5] => $li->approval_needed,
					$template_columns[6] => $li->approved,
					$template_columns[7] => $li->denied,
					$template_columns[8] => '<a href="' . $app->urlFor('view-brm', array('id' => $li->id)) . '">View</a>'
				);
			});

			$jsonArr = array_merge($jsonArr, $manager->createData($resource)->toArray());
			//$app->logger->addInfo(var_export($jsonArr, true));
			$app->view->renderJson($jsonArr);
		})->via('GET', 'POST');

		$app->post('/save(/:id)', function($id = NULL) use($app) {
			$created = time();
			if(is_null($id)) {
				// Create a new Request.
				$brm = \Model::factory('BRM\Campaign')->create();
				$brm->createdby = $app->user->id;
				$brm->created = $created;
			} else {
				$brm = \Model::factory('BRM\Campaign')->find_one($id);
			}

			// Creating the initial BRM Item.
			$brm->title = $app->request->post('name');
			$brm->description = $app->request->post('description');
			$brm->templateid = (($app->request->post('templateid') != '') ? $app->request->post('templateid') : NULL);
			$brm->population = (($app->request->post('population') != '') ? $app->request->post('population') : NULL);
			$brm->listname = (($app->request->post('emaillistname') != '') ? $app->request->post('emaillistname') : NULL);
				
			if(($app->request->post('launchdate') != '')) {
				$brm->launchdate = strtotime($app->request->post('launchdate'));	
			}

			$brm->save();

			$campaign = NULL;
			// Add Campaign to the system if it's new.
			if($app->request->post('campaigns') === 'new') {
				$campaign = \Model::factory('Campaign')->create();
				$campaign->name = $app->request->post('campaign-name');
				$campaign->description = $app->request->post('campaign-description');
				$campaign->created = $created;
				$campaign->createdby = $app->user->id;
				$campaign->save();
			} elseif(is_numeric($app->request->post('campaigns'))) {
				$campaign = \Model::factory('Campaign')->find_one($app->request->post('campaigns'));
			}

			if(!is_null($campaign)) {
				$brm->addCampaign($campaign);
			}

			// Request information.
			if(($app->request->post('requestdate') != '') ||
			   ($app->request->post('requestuser') != '') ||
			   ($app->request->post('department') != '')) {
			   	// Create a new Request Object.
				$request = \Model::factory('BRM\Request')->create();

				if(($app->request->post('requestdate') != '')) {
					$request->timestamp = strtotime($app->request->post('requestdate'));
				}

				if(($app->request->post('requestuser') != '')) {
					$req_user = \Model::factory('User')->where('email', $app->request->post('requestuser'))->find_one();
					if($req_user !== FALSE) {
						$request->addUser($req_user);
					} elseif($app->request->post('requestuser') !== '') {
						$request->email = $app->request->post('requestuser');
					}
				}

				if(($app->request->post('department') != '')) {
					$dept = \Model::factory('Department')->find_one($app->request->post('department'));
					if($dept !== FALSE) {
						$request->addDepartment($dept);
					}
				}

				$request->save();
				$brm->addRequest($request);
			}

			// Since the BRM has been saved, NOW, we need to create the new version in the database.
			// This needs to only happen if there is a change in the content.
			if(!isset($brm->current_version) ||
			   ($brm->currentVersion()->content !== $app->request->post('content') ||
			   $brm->currentVersion()->subject !== $app->request->post('contentsubject'))) {
				$version = \Model::factory('BRM\ContentVersion')->create();
				$version->content = $app->request->post('content');
				$version->subject = $app->request->post('contentsubject');
				$version->created = $created;
				$version->userid = $app->user->id;
				$brm->addVersion($version);
				$brm->addUsers((array) $app->request->post('users'), $app->request->post('permissions'));
			} else {
				// This is for those instances where the version doesn't change BUT the users might.
				$authusers = $brm->authorizedUsers()->find_array(); // Current users in the system.
				$authusers = array_column($authusers, 'userid');

				// The users to add.
				$add_users = array_diff((array) $app->request->post('users'), $authusers);
				// The users to remove.
				$rm_users = array_diff($authusers, (array) $app->request->post('users'));

				$brm->addUsers($add_users, $app->request->post('permissions'));

				$brm->removeUsers($rm_users);
			}

			// Now we need to create a state change if the BRM is Approved and the TemplateID has been set.
			if($brm->stateid === 2 && !is_null($brm->templateid)) {
				$statechange = \Model::factory('BRM\StateChange')->create();
				$statechange->userid = $app->user->id;
				$statechange->timestamp = $created;
				$statechange->stateid = 3;
				$brm->changeState($statechange);
			}

			// Now, we need to see if this is a save or notify action.
			if($app->request->post('submit') === 'send') {
				// Initiate a State Change and generate logins.
				if($brm->stateid == 0) {
					$statechange = \Model::factory('BRM\StateChange')->create();
					$statechange->userid = $app->user->id;
					$statechange->timestamp = $created;
					$statechange->stateid = 1;
					$brm->changeState($statechange);
				}

				if($brm->currentVersion()->brmversionid == 1) {
					$subject = 'New BRM Campaign: ' . $brm->title;
				} else {
					$subject = 'Updated BRM Campaign: ' . $brm->title;
				}

				$message_settings = array(
					'from_email' => $app->user->email,
					'from_name' => $app->user->firstname . ' ' . $app->user->lastname . ' (BRM Manager)',
					'subject' => $subject,
					'track_opens' => true
				);

				// Send E-mails to all the authorized users.
				foreach($brm->authorizedUsers()->find_many() as $authuser) {
					// Login the Users in and assign them to the email.
					$login = \ORM::for_table('login_attempts')->create();
					$user = $authuser->user();
					$login->userid = $user->id;
					$login->timestamp = $created;
					$login->hash = uniqid('user-' . $login->userid . '-');
					$login->authid = $authuser->id;
					$login_url = 'http://' . $_SERVER['HTTP_HOST'] . $app->urlFor('brm-login', array('brmid' => $brm->id, 'hash' => $login->hash));
					$message = array_merge($message_settings, array(
						'text' => str_replace(array('%USER%', '%URL%', '%AUTHOR%', '%VERB%', '%TITLE%'), 
											  array($user->email, $login_url, $app->user->firstname . ' ' . $app->user->lastname, 'created', $brm->title),
											  file_get_contents('../app/templates/email/approve.php')),
						'to' => array(array(
							'email' => $user->email,
							'name' => $user->firstname . " " . $user->lastname,
							'type' => 'to'
						))
					));
					$result = $app->mandrill->messages->send($message);

					$login->emailid = $result[0]['_id'];
					$login->save();
				}

			}

			// Redirect user to the newly created BRM Email.
			// Ignore the notify actions first.
			$app->redirect($app->urlFor('view-brm', array('id' => $brm->id)));			
		})->name('save-brm');

		$app->group('/view', function() use($app) {
			$app->get('/version/:id', function($id) use($app) {
				$data = \ORM::for_table('brm_content_version')->find_one($id);
				$json = array(
					'id' => $data->id,
					'subject' => $data->subject,
					'content' => $data->content,
					'created' => date('l, F j, Y g:i:s', $data->created)
				);

				$app->view->renderJson($json);
			})->name('view-version');

			$app->map('/:id(/:versionid)', function($id, $versionid = NULL) use($app) {
				$out = array();
				$out['brm_data'] = \Model::factory('BRM\Campaign')->find_one($id);
				// Grab the current version data as well ...
				$out['current_version'] = $out['brm_data']->currentVersion();

				// Grab the list of current users who are tied to the BRM.
				$out['auth_users'] = $out['brm_data']->authorizedUsers()->find_many();

				$out['authorized'] = FALSE;
				$out['editor'] = FALSE;
				foreach($out['auth_users'] as $authuser) {
					if($app->user->id === $authuser->userid) {
						if(Permissions::hasAccess((int)$authuser->permission, 'view')) {
							$out['authorized'] = $authuser->user();
							if(Permissions::hasAccess((int)$authuser->permission, 'edit')) {
								$out['editor'] = TRUE;
							}
						}
					}
				}

				// Determine if the current user is the creator/owner of the requested BRM
				$out['owner'] = FALSE;
				if($app->user->id == $out['brm_data']->createdby) {
					$out['owner'] = TRUE;
				}

				// If the user hasn't passed the authorized check, then see if they are an admin ...
				$out['admin'] = FALSE;
				if($app->user->hasAccess('admin') && !$out['authorized']) {
					$out['admin'] = TRUE;
				}

				if($app->request->isPost() && 
				  ((is_object($out['authorized'])) || 
				   ($out['admin'] === TRUE) ||
				   ($out['owner'] === TRUE))) {
					// This is adding a comment or approving the current BRM.
					if($app->request->post('action')) {
						switch($app->request->post('action')) {
							case 'addcomment':
								$comment = \ORM::for_table('comments')->create();
								$comment->userid = $app->user->id;
								$comment->brmid = $app->request->post('brmid');
								$comment->versionid = $app->request->post('versionid');
								break;
	
							case 'approve-version':
								$comment = \ORM::for_table('brm_auth_list')->find_one($out['authorized']->id);
								$comment->approved = 1;
								break;
	
							case 'deny-version':
								$comment = \ORM::for_table('brm_auth_list')->find_one($out['authorized']->id);
								$comment->approved = -1;
								break;
						}
						$comment->comment = $app->request->post('comment');
						$comment->timestamp = time();
						$comment->save();
					}

					if($app->request->post('changestate') && (($out['admin'] === TRUE) || ($out['owner'] === TRUE))) {
						$statechange = \Model::factory('BRM\StateChange')->create();
						$statechange->userid = $app->user->id;
						$statechange->timestamp = time();
						$statechange->stateid = $app->request->post('changestate');
						$out['brm_data']->changeState($statechange);
					}
				}

				if($out['authorized'] == FALSE) {
					$out['authorized'] = new stdClass();
				}

				$out['edit_url'] = $app->urlFor('edit-brm', array('id' => $id));

				$out['auth_users'] = $out['brm_data']->authorizedUsers()->find_many();

				// Grab all the header images associated with this version.
				$out['header_imgs'] = \ORM::for_table('brm_header_images')->where(array('brmid' => $out['brm_data']->id, 'brmversionid' => $out['current_version']->id));

				$out['previous_versions'] = $out['brm_data']->versions()->where_not_equal('id', $out['current_version']->id)->find_array();

				$out['comments'] = \ORM::for_table('view_brm_comments')->where('brmid', $out['brm_data']->id)
																  	   ->order_by_desc('timestamp', 'versionid')
																  	   ->find_many();

				$out['states'] = \Model::factory('BRM\State')->find_many();

				$app->view->appendJavascriptFile('/components/jquery.hotkeys/jquery.hotkeys.js');
				$app->view->appendJavascriptFile('/components/bootstrap-wysiwyg-steveathon/js/bootstrap-wysiwyg.min.js');

				$app->view->appendStylesheet('/components/bootstrap-wysiwyg-steveathon/css/style.css');

				$app->view->appendJavascriptFile('/js/viewbrm.js');
				$app->render('brm/view.php', $out);
			})->via('GET', 'POST')->name('view-brm');
		});

		$app->map('/create', function() use($app) {
			//$app->view->appendJavascriptFile('/components/handlebars/handlebars.min.js');
			$app->view->appendJavascriptFile('/components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js');
			//$app->view->appendJavascriptFile('/components/typeahead.js/dist/typeahead.bundle.min.js');
			$app->view->appendStylesheet('/components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css');
			$app->view->appendJavascriptFile('/components/select2/select2.min.js');
			$app->view->appendStylesheet('/components/select2/select2.css');
			$app->view->appendStylesheet('/components/select2-bootstrap-css/select2-bootstrap.min.css');
			$app->view->appendStylesheet('/css/brm-form.css');
			// Grab a list of users who have been used before.
			$users = \ORM::for_table('view_common_users')->find_many();
			$campaigns = \ORM::for_table('campaign')->find_many();
			$departments = \ORM::for_table('departments')->find_many();

			$app->view->appendJavascriptFile('/js/createbrm.js');
			$app->render('brm/create.php', array('users' => $users, 'campaigns' => $campaigns, 'departments' => $departments));
		})->via('GET', 'POST')->name('create-brm');

		$app->get('/edit/:id', function($id) use($app) {
			// Pull Data
			$data = array();
			$data['campaigns'] = \Model::factory('Campaign')->find_many();
			$data['departments'] = \Model::factory('Department')->find_many();
			$data['users'] = \ORM::for_table('view_common_users')->find_many();
			$data['brm'] = \Model::factory('BRM\Campaign')->find_one($id);
			$data['save'] = $app->urlFor('save-brm', array('id' => $id));

			$app->view->appendJavascriptFile('/components/handlebars/handlebars.min.js');
			$app->view->appendJavascriptFile('/components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js');
			$app->view->appendJavascriptFile('/components/typeahead.js/dist/typeahead.bundle.min.js');
			$app->view->appendStylesheet('/components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css');
			$app->view->appendJavascriptFile('/components/select2/select2.min.js');
			$app->view->appendStylesheet('/components/select2/select2.css');
			$app->view->appendStylesheet('/components/select2-bootstrap-css/select2-bootstrap.min.css');
			$app->view->appendStylesheet('/css/brm-form.css');
			$app->view->appendJavascriptFile('/js/createbrm.js');

			$app->render('brm/edit.php', $data);
		})->name('edit-brm');
	});

	$app->group('/user', $checkLogin, function() use($app, $checkPermissions) {
		$app->get('/view/:id', function($id) use($app) {
			$user = \Model::factory('User')->find_one($id);
			$brms = $user->brms()->find_many();
		})->name('view-user');

		$app->get('/search', function() use($app) {
			$query = '%' . trim($app->request->get('q')) . '%';

			$results = \ORM::for_table('user')->select(array('id', 'firstname', 'lastname', 'email'))
											  ->where_any_is(array(
											  	array('firstname' => $query),
											  	array('lastname' => $query),
											  	array('email' => $query)), 'LIKE')
											  ->limit(10)
											  ->find_many();

			$manager = new FractalManager();

			$resource = new FractalCollection($results, function($result) {
				return array(
					'id' => (int) $result->id,
					'firstname' => $result->firstname,
					'lastname' => $result->lastname,
					'email' => $result->email
				);
			});

			$jsonArr = $manager->createData($resource)->toArray();

			$app->view->renderJson($jsonArr['data']);
		});
		$app->map('/add', $checkPermissions('create'), function() use($app) { // Only Creators can add users to the system.
			if($app->request->isPost()) {

				$newUser = \BRMManager\Model\User::addUser($app->request->post());

				if($app->request->isAjax()) {
					$app->view->renderJson(array('userid' => $newUser->id, 'permissions' => $newUser->permissions));
				} else {
					$app->flashNow('success', 'New User Added.');
				}
			}

			if(!$app->user->hasAccess('admin')) {
				$app->flash('danger', 'You do not have the appropriate permissions to access this.');
				$app->redirect('/brm');
			}

			$app->render('user/form.php', array());
		})->via('GET', 'POST')->name('add-user');
	});

	$app->group('/image', function() use($app) {

	});

	$app->group('/admin', $checkLogin, $checkPermissions('admin'), function() use($app) {
		$app->get('/', function() use($app) {

		});

		$app->get('/cron', function() use($app) { // This is the function that will process the audit log for the application.

		});

		$app->group('/audit', function() use($app) {
			$app->post('/list', function() use($app) {

			})->name('audit-json');
			$app->get('/', function() use($app) {
				$app->view->appendJavascriptFile('/components/datatables/media/js/jquery.dataTables.min.js');
				$app->view->appendJavascriptFile('//cdn.datatables.net/plug-ins/f2c75b7247b/integration/bootstrap/3/dataTables.bootstrap.js');
				$app->view->appendStylesheet('//cdn.datatables.net/plug-ins/f2c75b7247b/integration/bootstrap/3/dataTables.bootstrap.css');
				$app->render('admin/audit.php', array());
			});
		});
	});

	$app->run();
