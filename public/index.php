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
	use \Monolog\Handler\StreamHandler;
	use \Monolog\Formatter\LineFormatter;
	use \Monolog\Handler\LogglyHandler;
	use \Monolog\Formatter\LogglyFormatter;
	use League\Flysystem\Filesystem;
	use League\Flysystem\Adapter\Local AS LocalFS;

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

		$stream = new StreamHandler('../logs/logger.log');
		$stream->setFormatter(new LineFormatter(null, null, true));

		$app->logger->pushHandler($stream);

		\ORM::configure('logging', true);
		\ORM::configure('logger', function($log_string, $query_time) use($app) {
    		$app->logger->addInfo('Query: ', array('query' => $log_string, 'time' => $query_time));
		});

		$app->view->appendJavascriptFile('/components/jquery/dist/jquery.min.js')
 				  ->appendJavascriptFile('/components/moment/min/moment.min.js')
				  ->appendJavascriptFile('/components/bootstrap/dist/js/bootstrap.min.js')
				  ->appendJavascriptFile('/components/jasny-bootstrap/dist/js/jasny-bootstrap.min.js')
				  ->appendJavascriptFile('/components/fuelux/dist/js/fuelux.min.js')
				  ->appendJavascriptFile('/components/jquery-ajax-progress/js/jquery.ajax-progress.js')
				  ->appendJavascriptFile('/components/handlebars/handlebars.min.js')
				  ->appendJavascriptFile('/components/typeahead.js/dist/typeahead.bundle.min.js')
				  ->appendJavascriptFile('/components/jqBootstrapValidation/dist/jqBootstrapValidation-1.3.7.min.js')
				  ->appendJavascriptFile('/components/datatables/media/js/jquery.dataTables.js')
				  ->appendJavascriptFile('//cdn.datatables.net/plug-ins/1.10.6/integration/bootstrap/3/dataTables.bootstrap.js')
				  ->appendJavascriptFile('/components/cheet.js/cheet.min.js');

		$app->view->appendStylesheet('/components/bootstrap/dist/css/bootstrap.min.css')
			 	  ->appendStylesheet('/components/jasny-bootstrap/dist/css/jasny-bootstrap.min.css')
			 	  ->appendStylesheet('/components/fontawesome/css/font-awesome.min.css')
			 	  ->appendStylesheet('//cdn.datatables.net/plug-ins/1.10.6/integration/bootstrap/3/dataTables.bootstrap.css')
			 	  ->appendStylesheet('/components/fuelux/dist/css/fuelux.min.css');

	});

	$app->configureMode('production', function() use($app) {
		
		$app->view->appendJavascriptFile('/components/jquery/dist/jquery.min.js')
 				  ->appendJavascriptFile('/components/moment/min/moment.min.js')
				  ->appendJavascriptFile('/components/bootstrap/dist/js/bootstrap.min.js')
				  ->appendJavascriptFile('/components/jasny-bootstrap/dist/js/jasny-bootstrap.min.js')
				  ->appendJavascriptFile('/components/fuelux/dist/js/fuelux.min.js')
				  ->appendJavascriptFile('/components/jquery-ajax-progress/js/jquery.ajax-progress.js')
				  ->appendJavascriptFile('/components/handlebars/handlebars.min.js')
				  ->appendJavascriptFile('/components/typeahead.js/dist/typeahead.bundle.min.js')
				  ->appendJavascriptFile('/components/jqBootstrapValidation/dist/jqBootstrapValidation-1.3.7.min.js')
				  ->appendJavascriptFile('/components/datatables/media/js/jquery.dataTables.js')
				  ->appendJavascriptFile('//cdn.datatables.net/plug-ins/1.10.6/integration/bootstrap/3/dataTables.bootstrap.js')
				  ->appendJavascriptFile('/components/cheet.js/cheet.min.js');

		$app->view->appendStylesheet('/components/bootstrap/dist/css/bootstrap.min.css')
			 	  ->appendStylesheet('/components/jasny-bootstrap/dist/css/jasny-bootstrap.min.css')
			 	  ->appendStylesheet('/components/fontawesome/css/font-awesome.min.css')
			 	  ->appendStylesheet('//cdn.datatables.net/plug-ins/1.10.6/integration/bootstrap/3/dataTables.bootstrap.css')
			 	  ->appendStylesheet('/components/fuelux/dist/css/fuelux.min.css');

	});

	$db_settings = include('../app/config/database.settings.php');
	$app->config('db_settings', $db_settings);

	\ORM::configure(sprintf('mysql:host=%s;dbname=%s', $db_settings['server'], $db_settings['dbname']));
	\ORM::configure('username', $db_settings['username']);
	\ORM::configure('password', $db_settings['password']);

	\Model::$auto_prefix_models = '\\BRMManager\\Model\\';

	$app->add(new \BRMManager\Middleware\User);

	$app->view->setLayout('layout/layout.php');

	// This is the default layout files.

	$app->view->appendJavascriptFile('/js/application.js')
			  ->appendStylesheet('/css/application.css');

	// Injecting Mandrill in to the app.
	$app->container->singleton('email', function() {
		$api_key = include_once('../app/config/mandrill.settings.php');
		return new \BRMManager\Email($api_key);
	});

	$checkLogin = function() use($app) {
		// Check the current Session and if the user is logged in.
		if(!$app->user || !$app->user->keepLoginAlive()) {
			$app->flash('info', 'Session Timed Out. Please re-login.');
			$app->redirect("/");
		} else {
			// Pass the User information on in to the system.
			$app->view->setLayoutData('user', $app->user);

			// Get User's Unread Child Comments.
			$comments = \ORM::for_table('comments')->table_alias('p')
												   ->select_many(array(
												 		'id' => 'c.id',
												 		'brmid' => 'c.brmid',
												 		'userid' => 'c.userid',
												 		'timestamp' => 'c.timestamp'))
												 ->join('comments', array('c.parentid', '=', 'p.id'), 'c')
												 ->where_gte('c.timestamp', $app->user->last_login)
												 //->where_raw('`c`.`timestamp` >= DATE_SUB(NOW(), INTERVAL 24 HOUR)')
												 ->where('p.userid', $app->user->id)->find_many();

			$app->view->setLayoutData('unread_comments', $comments);
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
					$login_attempt->set_expr('timestamp', 'NOW()');
	
					$login_attempt->hash = uniqid('user-' . $user->id . '-');
					$login_attempt->save();
	
					$login_url = 'http://' . $_SERVER['HTTP_HOST'] . $app->urlFor('verify-user', array('hash' => $login_attempt->hash));
					$message_details = include_once('../app/config/loginemail.settings.php');
					$app->email->template = file_get_contents('../app/templates/email/login.php');
					$app->email->replace(array('%USER%', '%URL%'), array($user->email, $login_url));
					$app->email->to($user->email, sprintf('%s %s', $user->firstname, $user->lastname));
					$app->email->from($message_details['from_email'], $message_details['from_name']);
					$app->email->subject($message_details['subject']);
	
					$result = $app->email->send();
					$app->logger->addInfo('Login Attempted: ' . $user->email);
					$app->logger->addInfo(var_export($result, true));

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
	
			$result = \ORM::for_table('login_attempts')->where('hash', $hash)->where_raw('UNIX_TIMESTAMP(`timestamp`) > ?', $range_time)->where_raw('UNIX_TIMESTAMP(`timestamp`) < ?', $curr_time)->find_one();
	
			if(!$result) {
				$app->flash('danger', 'Login Error, Please try again');
				$app->redirect('/');
			} else {
				$user = \ORM::for_table('user')->find_one($result->userid);
	
				$_SESSION['user'] = new \BRMManager\User\Session($user);

				$result->result = 1;
				$result->save();
	
				$app->redirect('/brm');
			}
		})->name('verify-user');

		$app->get('/brm/:brmid/:hash', function($brmid = NULL, $hash = NULL) use($app) {
			$login_attempt = \Model::factory('LoginAttempts')->where(array('hash' => $hash))->find_one();
			// Change the result and then log the user in.
			if(!$login_attempt) {
				$app->flash('danger', 'Login Error: Attempt does not exist.');
				$app->redirect('/');
			} else {
				$login_attempt->result = 1;
				$user = $login_attempt->user();
				$auth = $login_attempt->auth();
				$auth->set_expr('viewedtime', 'NOW()');
				$auth->save();
				$login_attempt->save();

				$_SESSION['user'] = new \BRMManager\User\Session($user);

				$app->redirect($app->urlFor('view-brm', array('id' => $auth->brmid)));
			}
		})->name('brm-login');
	});

	$app->group('/brm', $checkLogin, function() use($app) {
		$app->get('/', function() use($app) {
			$view = array();

			if($app->user->hasAccess('admin')) {
				$app->view->appendJavascriptFile('/js/main-admin.js');
				$view['admin'] = TRUE;
			} else {
				$app->view->appendJavascriptFile('/js/main.js');
				$view['admin'] = FALSE;
			}
			$app->render('brm/index.php', $view);
		});

		$app->map('/list', function() use($app) {
			// Grabbing post data and logging it.
			$app->logger->addInfo(var_export($app->request->post(), true));

			$template_columns = array(
				'id', 'title', 'state', 'createdby_name', 'launchdate', 'current_version', 'approval_needed', 'approved', 'denied', 'view', 'stateid'
			);

			// Grab the whole list of available table data.
			$list = \Model::factory('View\BRMList');

			$list->where_gte('stateid', '0');

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
					$template_columns[0]  => $li->id,
					$template_columns[1]  => $li->title,
					$template_columns[2]  => $li->state,
					$template_columns[3]  => $li->createdby_name,
					$template_columns[4]  => $li->launchdate,
					$template_columns[5]  => $li->brm_current_version,
					$template_columns[6]  => $li->approval_needed,
					$template_columns[7]  => $li->approved,
					$template_columns[8]  => $li->denied,
					$template_columns[9]  => '<a class="btn btn-default pull-right" href="' . $app->urlFor('view-brm', array('id' => $li->id)) . '">View</a>',
					$template_columns[10] => $li->stateid,
				);
			});

			$jsonArr = array_merge($jsonArr, $manager->createData($resource)->toArray());
			//$app->logger->addInfo(var_export($jsonArr, true));
			$app->logger->addInfo(json_encode($jsonArr));
			$app->view->renderJson($jsonArr);
		})->via('GET', 'POST');

		$app->post('/save(/:id)', function($id = NULL) use($app) {
			if(is_null($id)) {
				// Create a new Request.
				$brm = \Model::factory('BRM\Campaign')->create();
				$brm->createdby = $app->user->id;
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
				$brm->set_expr('launchdate', sprintf('FROM_UNIXTIME(%s)', strtotime($app->request->post('launchdate'))));	
			}

			$brm->save();

			$campaign = NULL;
			// Add Campaign to the system if it's new.
			if($app->request->post('campaigns') === 'new') {
				$campaign = \Model::factory('Campaign')->create();
				$campaign->name = $app->request->post('campaign-name');
				$campaign->description = $app->request->post('campaign-description');
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
					$request->set_expr('timestamp', sprintf('FROM_UNIXTIME(%s)', strtotime($app->request->post('requestdate'))));
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

			$statechange = \Model::factory('BRM\StateChange')->create();
			$statechange->userid = $app->user->id;
			if(is_null($brm->stateid) || ((int) $brm->stateid == 0 && $app->request->post('submit') === 'save')) {
				$statechange->stateid = 0;
			} elseif((int)$brm->stateid == 0 && $app->request->post('submit') === 'send') {
				$statechange->stateid = 1;
			} elseif((int)$brm->stateid === 2 && !is_null($brm->templateid)) {
				$statechange->stateid = 3;
			}

			if($statechange->is_dirty('stateid')) {
				$brm->changeState($statechange);
			}

			// Now, we need to see if this is a save or notify action.
			if($app->request->post('submit') === 'send') {
				// Generate logins.
				// Verb Changes.

				$counter_after = $brm->state_change()->where_gte('stateid', 1)->count();

				if($counter_after == 1) {
					$subject = 'New BRM Email Created: ' . $brm->title;
					$verb = 'created';
				} else {
					$subject = 'Updated BRM Email: ' . $brm->title;
					$verb = 'updated';
				}

				$app->email->subject($subject);
				$app->email->from($app->user->email, sprintf('%s %s (BRM Manager)', $app->user->firstname, $app->user->lastname));
				$app->email->template = file_get_contents('../app/templates/email/approve.php');

				// Send E-mails to all the authorized users.
				foreach($brm->authorizedUsers()->find_many() as $authuser) {
					// Login the Users in and assign them to the email.
					$login = \ORM::for_table('login_attempts')->create();
					$user = $authuser->user();
					$login->userid = $user->id;
					$login->hash = uniqid('user-' . $login->userid . '-');
					$login->authid = $authuser->id;
					$login_url = 'http://' . $_SERVER['HTTP_HOST'] . $app->urlFor('brm-login', array('brmid' => $brm->id, 'hash' => $login->hash));
					$app->email->replace(array('%USER%', '%URL%', '%AUTHOR%', '%VERB%', '%TITLE%'), array($user->email, $login_url, $app->user->firstname . ' ' . $app->user->lastname, $verb, $brm->title));
					$app->email->to($user->email, sprintf('%s %s', $user->firstname, $user->lastname));
					
					$result = $app->email->send();
					// Logging Email Data:
					$app->logger->addInfo('Email Sent to: ' . $user->email . ' For Email ID#' . $brm->id);
					$app->logger->addInfo(var_export($app->email->msg_arr, true));
					$app->logger->addInfo(var_export($result, true));
					$app->email->cleanEmail();
					
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
				$data = \Model::factory('BRM\ContentVersion')->filter('formatDate')->find_one($id);
				$json = array(
					'id' => $data->id,
					'subject' => $data->subject,
					'content' => $data->content,
					'created' => $data->created
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
					if((int) $app->user->id === (int) $authuser->userid) {
						$out['authorized'] = $authuser;

						if(is_null($out['authorized']->viewedtime)) {
							// Since this is being viewed now, lets get that viewed now.
							$out['authorized']->set_expr('viewedtime', 'NOW()');
							$out['authorized']->save();
						}

						if(Permissions::hasAccess((int)$authuser->permission, 'edit')) {
							$out['editor'] = TRUE;
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
				   	//$app->logger->addInfo(var_export($app->request->post(), true));
					// This is adding a comment or approving the current BRM.
					if($app->request->post('action')) {
						$comment = \ORM::for_table('comments')->create();
						switch($app->request->post('action')) {
							case 'approve-version':
								$out['authorized']->approved = 1;
								break;
	
							case 'deny-version':
								$out['authorized']->approved = -1;
								break;
						}

						// Need to have parent id in case it is a reply.
						if(!is_null($app->request->post('parentid'))) {
							$comment->parentid = $app->request->post('parentid');
						}

						// Also send out notification for comments.
						

						$comment->userid = $app->user->id;
						$comment->brmid = $app->request->post('brmid');
						$comment->versionid = $app->request->post('versionid');
						$comment->comment = $app->request->post('comment');
						$comment->save();

						if($app->request->post('action') != 'addcomment' &&
						   $app->request->post('action') != 'addcommentreply') {
							$out['authorized']->commentid = $comment->id;
							$out['authorized']->save();
						}
					}

					if($app->request->post('changestate') && (($out['admin'] === TRUE) || ($out['owner'] === TRUE))) {
						$statechange = \Model::factory('BRM\StateChange')->create();
						$statechange->userid = $app->user->id;
						// Switch on changestate:
						switch($app->request->post('changestate')) {
							case 2:
								if(!is_null($out['brm_data']->templateid)) {
									$statechange->stateid = 3;
								} else {
									$statechange->stateid = 2;
								}
								break;

							case 4:
								// We need to notify someone.
								$statechange->stateid = 4;
								// Load the user to notify:
								$notify_user = \Model::factory('User')->find_one($app->request->post('pubnotify'));

								// Load the notification email:
								$app->email->template = file_get_contents('../app/templates/email/published.php');
								$app->email->replace(array(), array());
								$app->email->from($app->user->email, sprintf('%s %s (BRM Manager)', $app->user->firstname, $app->user->lastname));
								$app->email->to($notify_user->email, sprintf('%s %s', $notify_user->firstname, $notify_user->lastname));
								$app->email->subject('BRM Published Notification: ' . $out['brm_data']->title);
								// TODO: Track the notification emails being sent out.
								$app->email->send();

								$app->flashNow('info', 'User Has Been Notified.');
								break;

							case 5:
								// We need to notify EVERYONE.
								$statechange->stateid = 5;
								break;

							default:
								$statechange->stateid = $app->request->post('changestate');
								break;
						}
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

				$out['comments'] = \Model::factory('BRM\Comment')->where('brmid', $out['brm_data']->id)
																 ->where_null('parentid')
																 ->order_by_desc('timestamp', 'versionid')
																 ->find_many();

				$out['states'] = \Model::factory('BRM\State')->find_many();

				$out['notify_users'] = \Model::factory('User')->find_many();

				$app->logger->addInfo('View BRM ID#' . $out['brm_data']->id);
				$app->logger->addInfo(var_export($out['authorized'], true));

				$app->view->appendJavascriptFile('/components/jquery.hotkeys/jquery.hotkeys.js');
				$app->view->appendJavascriptFile('/components/bootstrap-wysiwyg-steveathon/js/bootstrap-wysiwyg.min.js');
				$app->view->appendJavascriptFile('/components/select2/select2.min.js');
				$app->view->appendStylesheet('/components/select2/select2.css');
				$app->view->appendStylesheet('/components/select2-bootstrap-css/select2-bootstrap.min.css');
				$app->view->appendStylesheet('/components/bootstrap-wysiwyg-steveathon/css/style.css');

				$app->view->appendJavascriptFile('/js/viewbrm.js');
				$app->render('brm/view.php', $out);
			})->via('GET', 'POST')->name('view-brm');
		});

		$app->map('/create', function() use($app) {
			$data = array();
			$app->view->appendJavascriptFile('/components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js');
			$app->view->appendStylesheet('/components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css');
			$app->view->appendJavascriptFile('/components/select2/select2.min.js');
			$app->view->appendStylesheet('/components/select2/select2.css');
			$app->view->appendStylesheet('/components/select2-bootstrap-css/select2-bootstrap.min.css');
			$app->view->appendStylesheet('/css/brm-form.css');
			// Removing Common users. Replacing with User Groups.
			// Grab a list of users who have been used before.
			$data['user_groups'] = \Model::factory('BRM\AuthGroup')->find_many();
			$data['campaigns'] = \Model::factory('Campaign')->find_many();
			$data['departments'] = \Model::factory('Department')->find_many();

			$app->view->appendJavascriptFile('/js/createbrm.js');
			$app->render('brm/create.php', $data);
		})->via('GET', 'POST')->name('create-brm');

		$app->get('/edit/:id', function($id) use($app) {
			// Pull Data
			$data = array();
			$data['campaigns'] = \Model::factory('Campaign')->find_many();
			$data['departments'] = \Model::factory('Department')->find_many();
			//$data['users'] = \ORM::for_table('view_common_users')->find_many();
			$data['user_groups'] = \Model::factory('BRM\AuthGroup')->find_many();
			$data['brm'] = \Model::factory('BRM\Campaign')->find_one($id);
			$data['save'] = $app->urlFor('save-brm', array('id' => $id));

			$app->view->appendJavascriptFile('/components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js');
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
		$app->group('/groups', $checkPermissions('create'), function() use($app) {
			$app->get('/', function() use($app) {
				$app->view->appendJavascriptFile('/js/usergroupindex.js');

				$usergroups = \Model::factory('BRM\AuthGroup')->find_many();

				$app->render('user/groups/index.php', array('usergroups' => $usergroups));
			});

			$app->map('/edit/:id', function($id) use($app) {
				$usergroup = \Model::factory('BRM\AuthGroup')->find_one($id);
				if($app->request->isPost()) {
					$usergroup->name = $app->request->post('name');
					$usergroup->description = $app->request->post('description');
					$usergroup->save();

					// Grab Users already in the system.
					$members = $usergroup->members()->select('userid')->find_array();
					$members = array_map(function($val) {
						return $val['userid'];
					}, $members);

					$sub_users = $app->request->post('users');
					$rm_members = array_diff($members, $sub_users);
					$new_members = array_diff($sub_users, $members);

					// Add Users to AuthGroup.
					$usergroup->addMembers($new_members);

					$usergroup->deleteMembers($rm_members);

					$app->flashNow('success', 'User Group Saved.');
				}

				// Grabbing current user list.
				$users = \Model::factory('User')->find_many();

				$app->view->appendJavascriptFile('/components/select2/select2.min.js');
				$app->view->appendStylesheet('/components/select2/select2.css');
				$app->view->appendStylesheet('/components/select2-bootstrap-css/select2-bootstrap.min.css');
				$app->view->appendJavascript('$("#userSelect").select2();');
				$app->render('user/groups/form.php', array('usergroup_info' => $usergroup, 'users' => $users));
			})->via('GET', 'POST')->name('edit-user-groups');

			$app->map('/add', function() use($app) {
				$usergroup = \Model::factory('BRM\AuthGroup')->create();
				if($app->request->isPost()) {
					$usergroup->name = $app->request->post('name');
					$usergroup->description = $app->request->post('description');
					$usergroup->save();

					// Add Users to AuthGroup.
					$usergroup->addMembers($app->request->post('users'));

					$app->flash('success', sprintf('User Group <strong>%s</strong> created.', $usergroup->name));
					$app->redirect($app->urlFor('edit-user-groups', array('id' => $usergroup->id)));
				}

				// Grabbing current user list.
				$users = \Model::factory('User')->find_many();

				$app->view->appendJavascriptFile('/components/select2/select2.min.js');
				$app->view->appendStylesheet('/components/select2/select2.css');
				$app->view->appendStylesheet('/components/select2-bootstrap-css/select2-bootstrap.min.css');
				$app->view->appendJavascript('$("#userSelect").select2();');
				$app->render('user/groups/form.php', array('usergroup_info' => $usergroup, 'users' => $users));
			})->via('GET', 'POST')->name('add-user-group');
		});

		$app->get('/', $checkPermissions('create'), function() use($app) {
			$users = \Model::factory('User')->select('*')->select_expr("DATE_FORMAT(`created`, '%b %d, %Y %l:%i:%s %p')", 'created')->find_many();
			$app->view->appendJavascriptFile('/js/userindex.js');

			$app->render('user/index.php', array('users' => $users));
		});

		$app->map('/edit/:id', $checkPermissions('create'), function($id) use($app) {
			$app->view->appendJavascriptFile('/js/userform.js');
			$user = \Model::factory('User')->find_one($id);
			if($app->request->isPost()) {
				$user->email = $app->request->post('email');
				$user->firstname = $app->request->post('firstname');
				$user->lastname = $app->request->post('lastname');
				$user->permissions = $app->request->post('permissions');

				$user->save();

				$app->flashNow('info', 'User Updated.');
			}
			
			$app->render('user/form.php', array('user_data' => $user));
		})->via('GET', 'POST')->name('edit-user');

		$app->get('/search', function() use($app) {
			$query = '%' . trim($app->request->get('q')) . '%';

			$results = \Model::factory('User')->select(array('id', 'firstname', 'lastname', 'email'))
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
					$app->flash('success', 'New User Added.');
					$app->redirect($app->urlFor('edit-user', array('id' => $newUser->id)));
				}
			}

			if(!$app->user->hasAccess('admin')) {
				$app->flash('danger', 'You do not have the appropriate permissions to access this.');
				$app->redirect('/brm');
			}

			$user = \Model::factory('User')->create();
			$app->view->appendJavascriptFile('/js/userform.js');
			$app->render('user/form.php', array('user_data' => $user));
		})->via('GET', 'POST')->name('add-user');
	});

	$app->group('/search', $checkLogin, function() use($app) {
		$app->group('/brm', function() use($app) {
			$app->get('/title', function() use($app) {
				if(is_null($app->request->get('q'))) {
					$app->flash('warning', 'No Search Query Entered');
					$app->redirect('/brm');
				}
				$results = \Model::factory('BRM\Campaign')->filter('title', $app->request->get('q'))->find_many();

				//$app->logger->addInfo(var_export($results, true));
				if($app->request->isAjax()) {
					$manager = new FractalManager();

					$resource = new FractalCollection($results, function($result) use($app) {
						return array(
							'link' => $app->urlFor('view-brm', array('id' => $result->id)),
							'value' => $result->title,
						);
					});

					$jsonArr = $manager->createData($resource)->toArray();
					$app->view->renderJson($jsonArr['data']);
				} else {
					$app->render('search/search.php', array('title' => 'Search By BRM Title', 'results' => $results));
				}
			});

			$app->get('/description', function() use($app) {

			});

			$app->get('/templateid', function() use($app) {

			});

			$app->group('/content', function() use($app) {
				$app->get('/body', function() use($app) {

				});

				$app->get('/title', function() use($app) {

				});
			});
		});

		/* $app->get('/campaign', function() use($app) {

		}); */
	});

	$app->group('/image', function() use($app) {

	});

	$app->group('/admin', $checkLogin, $checkPermissions('admin'), function() use($app) {
		$app->group('/brm', function() use($app) {
			$app->post('/delete', function() use($app) {
				if($app->request->isPost()) {
					//var_dump($app->request->post());
					if(!is_null($app->request->post('deleteId'))) {
						switch($app->request->post('deleteoption')) {
							case 'delete':
								// Grab BRM to delete.
								$brm = \Model::factory('BRM\Campaign')->find_one($app->request->post('deleteId'));
								$brm->delete();

								$app->flash('success', 'BRM has been deleted');
								break;

							case 'changestate':
								$brm = \Model::factory('BRM\Campaign')->find_one($app->request->post('deleteId'));
								$statechange = \Model::factory('BRM\StateChange')->create();
								$statechange->userid = $app->user->id;
								$statechange->stateid = -1;
								$brm->changeState($statechange);

								$app->flash('success', 'BRM has been hidden.');
								break;
						}
					} else {
						$app->flash('warning', 'No BRM action was taken because BRMID was not set.');
					}

					$app->redirect('/brm');
				}
			})->name('delete-brm');
		});
		$app->map('/er', function() use($app) {
			$db_settings = $app->config('db_settings');
			$_GET['db'] = $db_settings['dbname'];
			$_GET['username'] = $db_settings['username'];

			$app->view->disableLayout();
			include('../bin/adminer.php');
		})->via('GET', 'POST');

		$app->get('/migrations', function() use($app) { // This is a simple migration system.
			$adapter = new LocalFS(__DIR__ . '/db');
			$filesystem = new Filesystem($adapter);

			$data = array('table' => array());

			foreach($filesystem->listContents() as $contents) {
				// Check to see if we have run this migration before.
				$count = \ORM::for_table('migrations')->where('name', $contents['filename'])->count();
				if($app->request->get('run') == $contents['filename']) {
					if($count != false) {
						// This has been run before.
						$app->flash('danger', '<strong>Migration has been run before on this database.</strong>');
						$app->redirect('/admin/migrations');
					}

					// We run this migration and die.
					include_once(__DIR__ . '/db/' . $contents['path']);

					// Save Migration:
					$migration = \ORM::for_table('migrations')->create();

					$migration->name = $contents['filename'];
					$migration->set_expr('timestamp', 'NOW()');
					$migration->save();

					echo "\n\n" . '<p><a href="/admin/migrations">Go Back</a></p>';
					die(); 
				}

				$data['table'][] = array(
					'name' => $contents['filename'],
					'link' => sprintf('<a href="/admin/migrations?run=%s">Run Migration</a>', $contents['filename']),
					'count' => $count
				);
			}

			$app->render('admin/migrations.php', $data);
		});

		$app->get('/', function() use($app) {
			$app->render('admin/index.php', array());
		});

		$app->get('/cron', function() use($app) { // This is the function that will process the audit log for the application.

		});

		$app->group('/audit', function() use($app) {
			$app->post('/list', function() use($app) {

			})->name('audit-json');
			$app->get('/', function() use($app) {
				$app->render('admin/audit.php', array());
			});
		});
	});

	$app->run();
