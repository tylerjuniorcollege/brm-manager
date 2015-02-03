<?php
	/** BRM Manager
	 * App for getting approval from multiple users and managing multiple campaigns.
	 *
	 * Written by: Duane Jeffers <djef@tjc.edu>
	 **/

	require_once('../vendor/autoload.php');

	session_cache_limiter(false);
	session_start();

	$app = new \Slim\Slim(array(
		'debug' => true,
		'view' => new \TJC\View\Layout(),
		'templates.path' => '../app/templates',
		'whoops.editor' => 'sublime'
	));

	\ORM::configure('sqlite:../data/database.db');

	$app->add(new \Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware);

	$app->view->setLayout('layout/layout.php');

	// This is the default layout files.
 	$app->view->appendJavascriptFile('/components/jquery/dist/jquery.min.js')
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
		if(!isset($_SESSION['user']) || !$_SESSION['user']->keepLoginAlive()) {
			$app->flash('info', 'Session Timed Out. Please re-login.');
			$app->redirect("/");
		} else {
			// Pass the User information on in to the system.
			$app->view->setLayoutData('user', $_SESSION['user']);
		}
	};

	$app->get('/', function() use($app) {
		$app->render('index.php', array());
	});

	$app->post('/login', function() use($app) {
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

	$app->get('/logout', function() use($app) {
		unset($_SESSION['user']);
		$app->flash('info', 'User is now logged out.');
		$app->redirect("/");
	});

	$app->get('/verify/:hash', function($hash = null) use($app) {
		$curr_time = time();
		$range_time = $curr_time - (20 * 60);

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

	$app->group('/brm', $checkLogin, function() use($app) {
		$app->get('/', function() use($app) {
			$view = array();
			if($_SESSION['user']->hasAccess('create')) {
				// gather all of the BRMs that they have created ....
				$created = \ORM::for_table('view_create_brm_list')->where('createdby', $_SESSION['user']->id)->find_many();

				$view['created'] = $created;
			}

			$app->render('brm/index.php', $view);
		});

		$app->get('/view/:id', function($id) use($app) {

		})->name('view-brm');
	});

	$app->group('/user', function() use($app) {

	});

	$app->run();