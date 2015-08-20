<?php

namespace BRMManager\Model\BRM;

class AuthList
	extends \Model
{
	public static $_table = 'brm_auth_list';

	public function user() {
		return $this->has_one('User', 'id', 'userid')->find_one();
	}
}