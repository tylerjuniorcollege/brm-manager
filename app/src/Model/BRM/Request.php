<?php

namespace BRMManager\Model\BRM;
use BRMManager\Model\Department;
use BRMManager\Model\User;

class Request
	extends \Model
{
	public static $_table = 'brm_requests';

	public function user() {
		return $this->has_one('User', 'id', 'userid')->find_one();
	}

	public function addUser(User $user) {
		$this->userid = $user->id;
	}

	public function department() {
		return $this->has_one('Department', 'id', 'departmentid')->find_one();
	}

	public function addDepartment(Department $department) {
		$this->departmentid = $department->id;
	}
}