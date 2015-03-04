<?php

namespace BRMManager\Model\BRM;
use BRMManager\Model\Department;
use BRMManager\Model\User;

class Request
	extends \Model
{
	public static $_table = 'brm_requests';

	public function addUser(User $user) {
		$this->userid = $user->id;
	}

	public function addDepartment(Department $department) {
		$this->departmentid = $department->id;
	}
}