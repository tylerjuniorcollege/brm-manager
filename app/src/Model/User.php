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
		$newUser->permissions = $post_arr['permissions'] + 1; // Auto Assign 'View' Permission.
		$newUser->created = time();
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
}