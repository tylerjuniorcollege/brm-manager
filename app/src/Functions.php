<?php

function adminer_object() {
	class AdminerSoftware extends Adminer {
		function name() {
			return '<a href="/brm">BRM Manager</a>';
		}

		function credentials() {
			$app = \Slim\Slim::getInstance();

			$app->config('');
			return array();
		}
	}

	return new AdminerSoftware;	
}