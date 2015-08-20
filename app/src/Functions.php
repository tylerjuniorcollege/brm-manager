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

function outputComments($comments) {
	// This is a recursive function to output comments with children.

	// Get Application to render html:
	$app = \Slim\Slim::getInstance();

	$data = array();

	foreach($comments as $comment) {
		$background = NULL;

		$auth = $comment->authList();
		if($auth) {
			switch((int) $auth->approved) {
				case 1:
					$background = ' bg-success';
					break;

				case -1:
					$background = ' bg-danger';
					break;
			}
		}

		// Check to see if children exist.
		$child_count = $comment->children()->count();
		if($child_count > 0) {
			$child_comments = outputComments($comment->children()->order_by_desc('timestamp', 'versionid')->find_many());
		} else {
			$child_comments = NULL;
		}

		$user = $comment->user();
		$version = $comment->version();

		$data[] = $app->view->partial('partial/comments.php', array(
			'background'  	 => $background,
			'commentid'		 => $comment->id,
			'comment'		 => $comment->comment,
			'avatar' 		 => \BRMManager\Gravatar\genUrl($user->email),
			'name' 			 => sprintf('%s %s', $user->firstname, $user->lastname),
			'posted'		 => date('l, F j, Y g:i:s', strtotime($comment->timestamp)),
			'versionid' 	 => $version->brmversionid,
			'child_comments' => $child_comments
		));
	}

	return implode("\n", $data);
}