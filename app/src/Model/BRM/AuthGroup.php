<?php

namespace BRMManager\Model\BRM;

class AuthGroup
	extends \Model {

	public static $_table = 'brm_auth_group';

	public function members() {
		return $this->has_many('BRM\AuthGroupMembers', 'groupid');
	}

	public function addMembers($user_id) {
		if(!is_array($user_id)) {
			$user_id = (array)$user_id;
		}

		foreach($user_id as $user) {
			$member = \Model::factory('BRM\AuthGroupMembers')->create();
			$member->userid = $user;
			$member->groupid = $this->id;
			$member->save();
		}
	}

	public function deleteMembers($user_id) {
		if(!is_array($user_id)) {
			$user_id = (array)$user_id;
		}

		foreach($user_id as $user) {
			$member = \Model::factory('BRM\AuthGroupMembers')->where(array('userid' => $user, 'groupid' => $this->id))->find_one();
			$member->delete();
		}		
	}
}