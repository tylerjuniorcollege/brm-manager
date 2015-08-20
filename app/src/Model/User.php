<?php

namespace BRMManager\Model;

class User
	extends \Model 
{
	public static $_table = 'user';

	public static function addUser($post_arr) {
		$newUser = \Model::factory('User')->create();
		$newUser->firstname = $post_arr['firstname'];
		$newUser->lastname = $post_arr['lastname'];
		$newUser->email = strtolower($post_arr['email']);
		$newUser->permissions = $post_arr['permissions'];
		$newUser->set_expr('created', 'NOW()');
		$newUser->save();

		return $newUser;
	}

	public function brms() {
		// This Grabs the BRMs that the user is associated with.
		return $this->has_many('BRM\Campaign', 'createdby');
	}

	public function comments() {
		return $this->has_many('BRM\Comment', 'userid');
	}

	public function editLink() {
		$app = \Slim\Slim::getInstance();
		return $app->urlFor('edit-user', array('id' => $this->id));
	}

	public function lastLogin() {
		$last_login = $this->has_many('LoginAttempts', 'userid')->where('result', 1)->order_by_desc('timestamp')->find_one();
		return $last_login->timestamp;
	}
}