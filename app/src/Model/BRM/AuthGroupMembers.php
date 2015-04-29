<?php

namespace BRMManager\Model\BRM;

class AuthGroupMembers
	extends \Model {

	public static $_table = 'brm_auth_group_members';

	public function user()
	{
		return $this->belongs_to('User', 'userid');
	}

	public function groups()
	{
		return;
	}
}