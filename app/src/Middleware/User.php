<?php

	/**
	 * \BRMManager\Middleware\User is service to register and inject the user object in to the layout.
	 **/

namespace BRMManager\Middleware;

class User extends \Slim\Middleware 
{
	public function call() {
		// Creating user singleton.
		$this->app->container->singleton('user', function() {
			if(isset($_SESSION['user']) && is_object($_SESSION['user'])) {
				return $_SESSION['user'];
			} else {
				return FALSE;
			}
		});

		// inject the user object into the Layout.
		if($this->app->user && $this->app->user->keepLoginAlive()) {
			$this->app->view->setLayoutData('user', $this->app->user);
		}

		$this->next->call();
	}
}