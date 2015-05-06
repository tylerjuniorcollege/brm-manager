<?php

function adminer_object() {
	class AdminerSoftware extends Adminer {
		function name() {
			return '<a href="/brm">BRM Manager</a>';
		}

		function credentials() {
			$app = \Slim\Slim::getInstance();

			$db_settings = $app->config('db_settings');
			return array($db_settings['server'], $db_settings['username'], $db_settings['password']);
		}

		function database() {
			$app = \Slim\Slim::getInstance();

			$db_settings = $app->config('db_settings');
			return $db_settings['dbname'];
		}
	}

	return new AdminerSoftware;	
}