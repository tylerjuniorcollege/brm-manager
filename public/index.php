<?php
	/** BRM Manager
	 * App for getting approval from multiple users and managing multiple campaigns.
	 *
	 * Written by: Duane Jeffers <djef@tjc.edu>
	 **/

	require_once('../vendor/autoload.php');

	session_cache_limiter(false);
	session_start();

	use \League\Fractal\Manager AS FractalManager;
	use \League\Fractal\Resource\Collection as FractalCollection;
	use \BRMManager\Permissions as Permissions;

	$app = new \Slim\Slim(array(
		'debug' => true,
		'view' => new \TJC\View\Layout(),
		'templates.path' => '../app/templates',
		'whoops.editor' => 'sublime'
	));

	\ORM::configure('sqlite:../data/database.db');
	\ORM::configure('logging', false);
	\ORM::configure('logger', function($log_string, $query_time) {
    	var_dump($log_string);
	});

	\Model::$auto_prefix_models = '\\BRMManager\\Model\\';

	$app->add(new \BRMManager\Middleware\User);
	$app->add(new \Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware);

	$app->view->setLayout('layout/layout.php');

	// This is the default layout files.
 	$app->view->appendJavascriptFile('/components/jquery/dist/jquery.min.js')
 			  ->appendJavascriptFile('/components/moment/min/moment.min.js')
			  ->appendJavascriptFile('/components/bootstrap/dist/js/bootstrap.min.js')
			  ->appendJavascriptFile('/components/jasny-bootstrap/dist/js/jasny-bootstrap.min.js')
			  ->appendJavascriptFile('/components/jquery-ajax-progress/js/jquery.ajax-progress.js')
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

				$_SESSION['user'] = new \BRMManager\User\Session($user);

				$app->redirect($app->urlFor('view-brm'), array('id' => $auth->brmid));
			}
		})->name('brm-login');
	});

	$app->group('/brm', $checkLogin, function() use($app) {
		$app->get('/', function() use($app) {
			$view = array();
			if($_SESSION['user']->hasAccess('create')) {
				if(!empty($app->request->get('create_page'))) {
					$offset = ((int) $app->request->get('create_page') * 10);
				} else {
					$offset = 0;
				}
				// gather all of the BRMs that they have created ....
				$created = \ORM::for_table('view_brm_list_approve')->where('createdby', $_SESSION['user']->id)->limit(10)->offset($offset)->find_many();

				$view['created'] = $created;
			}

			if($_SESSION['user']->hasAccess('approve')) {

			}

			$app->render('brm/index.php', $view);
		});

		$app->map('/save(/:id)', function($id = NULL) use($app) {
			if(is_null($id)) {
				// Create a new Request.
				$brm = \Model::factory('BRM\Campaign')->create();
			} else {
				$brm = \Model::factory('BRM\Campaign')->find_one($id);
			}

			// Creating the initial BRM Item.
			$brm->title = $app->request->post('name');
			$brm->description = $app->request->post('description');
			$brm->templateid = (!empty($app->request->post('templateid')) ? $app->request->post('templateid') : NULL);
			$brm->population = (!empty($app->request->post('population')) ? $app->request->post('population') : NULL);
			$brm->listname = (!empty($app->request->post('listname')) ? $app->request->post('listname') : NULL);
				
			if(!empty($app->request->post('launchdate'))) {
				$brm->launchdate = strtotime($app->request->post('launchdate'));	
			}

			$brm->createdby = $app->user->id;
			$brm->created = $created;
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
				if(!empty($app->request->post('requestdate')) ||
				   !empty($app->request->post('requestuser')) ||
				   !empty($app->request->post('department'))) {
				   	// Create a new Request Object.
					$request = \Model::factory('BRM\Request')->create();

					if(!empty($app->request->post('requestdate'))) {
						$request->timestamp = strtotime($app->request->post('requestdate'));
					}

					if(!empty($app->request->post('requestuser'))) {
						$req_user = \Model::factory('User')->where('email', $app->request->post('requestuser'))->find_one();
						$request->addUser($req_user);
					}

					if(!empty($app->request->post('department'))) {
						$dept = \Model::factory('Department')->find_one($app->request->post('department'));
						$request->addDepartment($dept);
					}

					$request->save();
					$brm->addRequest($request);

				}

				// Since the BRM has been saved, NOW, we need to create the new version in the database.
				$version = \Model::factory('BRM\ContentVersion')->create();
				$version->content = $app->request->post('content');
				$version->created = $created;
				$version->userid = $app->user->id;
				$brm->addVersion($version);

				$brm->addUsers((array) $app->request->post('users'), $app->request->post('permissions'));

				// Now, we need to see if this is a save or notify action.
				if($app->request->post('submit') === 'send') {
					// Initiate a State Change and generate logins.
					$statechange = \Model::factory('BRM\StateChange')->create();
					$statechange->userid = $app->user->id;
					$statechange->timestamp = $created;
					$statechange->stateid = 1;
					$brm->changeState($statechange);

					$message_settings = include_once('../app/config/loginemail.settings.php');

					// Send E-mails to all the authorized users.
					foreach($brm->authorizedUsers() as $authuser) {
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
												  array($user->email, $login_url, $app->user->firstname . ' ' . $app->user->lastname, 'created', $brm->name), 
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

			
		})->via('POST')->name('save-brm');

		$app->group('/view', function() use($app) {
			$app->map('/:id(/:versionid)', function($id, $versionid = NULL) use($app) {
				$out = array();
				$out['brm_data'] = \Model::factory('BRM\Campaign')->find_one($id);
				// Grab the current version data as well ...
				$out['current_version'] = $out['brm_data']->currentVersion();

				// Grab the list of current users who are tied to the BRM.
				$out['auth_users'] = $out['brm_data']->authorizedUsers();

				$isAuthorized = FALSE;
				foreach($out['auth_users'] as $authuser) {
					if($app->user->id === $authuser->userid) {
						if(Permissions::hasAccess((int)$user->permission, 'view')) {
							$isAuthorized = $authuser->user();
						}
					}
				}

				// Determine if the current user is the creator/owner of the requested BRM
				$out['owner'] = FALSE;
				if($app->user->id == $out['brm_data']->createdby) {
					$out['owner'] = TRUE;
					if(!$isAuthorized) {
						$isAuthorized = \Model::factory('User')->find_one($app->user->id);
					}
				}

				// If the user hasn't passed the authorized check, then see if they are an admin ...
				$out['admin'] = FALSE;
				if($app->user->hasAccess('admin') && !$isAuthorized) {
					$isAuthorized = \Model::factory('User')->find_one($app->user->id);
					$out['admin'] = TRUE;
				}

				if($app->request->isPost() && is_object($isAuthorized)) {
					// This is adding a comment or approving the current BRM.
					switch($app->request->post('action')) {
						case 'addcomment':
							$comment = \ORM::for_table('comments')->create();
							$comment->userid = $app->user->id;
							$comment->brmid = $app->request->post('brmid');
							$comment->versionid = $app->request->post('versionid');
							break;

						case 'approve':
							$comment = \ORM::for_table('brm_auth_list')->find_one($isAuthorized->id);
							$comment->approved = 1;
							break;

						case 'deny':
							$comment = \ORM::for_table('brm_auth_list')->find_one($isAuthorized->id);
							$comment->approved = -1;
							break;
					}
					$comment->comment = $app->request->post('comment');
					$comment->timestamp = time();
					$comment->save();
				}

				$out['auth_users'] = $out['brm_data']->authorizedUsers();

				// Grab all the header images associated with this version.
				$out['header_imgs'] = \ORM::for_table('brm_header_images')->where(array('brmid' => $out['brm_data']->id, 'brmversionid' => $out['current_version']->id));

				$out['previous_versions'] = $out['brm_data']->versions()->where_not_equal('id', $out['current_version']->id)->find_array();

				$out['comments'] = \ORM::for_table('view_brm_comments')->where('brmid', $out['brm_data']->id)
																  	   ->order_by_desc('timestamp', 'versionid')
																  	   ->find_many();

				$app->view->appendJavascriptFile('/js/viewbrm.js');
				$app->render('brm/view.php', $out);
			})->via('GET', 'POST')->name('view-brm');

			$app->get('/version/:id', function($id) use($app) {
				$data = \ORM::for_table('brm_content_version')->find_one($id);
				$json = array(
					'id' => $data->id,
					'content' => $data->content,
					'created' => date('l, F j, Y g:i:s', $data->created)
				);

				$app->view->renderJson($json);
			})->name('view-version');
		});

		$app->map('/create', function() use($app) {
			/* if($app->request->isPost()) {
				$created = time();
				// Change to BRM Campaign.
				$brm = Model::factory('BRM\Campaign')->create();

			} */
			$app->view->appendJavascriptFile('/components/handlebars/handlebars.min.js');
			$app->view->appendJavascriptFile('/components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js');
			$app->view->appendJavascriptFile('/components/typeahead.js/dist/typeahead.bundle.min.js');
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

		$app->map('/edit/:id', function($id) use($app) {
			// Pull Data

			$app->view->appendJavascriptFile('/components/handlebars/handlebars.min.js');
			$app->view->appendJavascriptFile('/components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js');
			$app->view->appendJavascriptFile('/components/typeahead.js/dist/typeahead.bundle.min.js');
			$app->view->appendStylesheet('/components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css');
			$app->view->appendJavascriptFile('/components/select2/select2.min.js');
			$app->view->appendStylesheet('/components/select2/select2.css');
			$app->view->appendStylesheet('/components/select2-bootstrap-css/select2-bootstrap.min.css');
			$app->view->appendStylesheet('/css/brm-form.css');

			$campaigns = \Model::factory('Campaign')->find_many();
			$departments = \Model::factory('Department')->find_many();
			$users = \ORM::for_table('view_common_users')->find_many();

			$app->render('brm/edit.php', array('campaigns' => $campaigns, 'departments' => $departments, 'users' => $users));
		})->via('GET', 'POST')->name('edit-brm');
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
