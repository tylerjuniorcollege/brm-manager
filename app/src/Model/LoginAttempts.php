<?php

namespace BRMManager\Model;

class LoginAttempts
	extends \Model
{
	public static $_table = 'login_attempts';

	public function auth() {
		if(!is_null($this->authid)) {
			return $this->has_one('BRM\AuthList', 'id', 'authid')->find_one();
		} else
			return FALSE;
	}

	public function user() {
		return $this->has_one('User', 'id', 'userid')->find_one();
	}

	public function hash() {
		if(is_null($this->hash)) {
			
		}
	}
}