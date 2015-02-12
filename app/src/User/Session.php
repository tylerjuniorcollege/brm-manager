<?php
// This is a User Session object, used to pass cached data about the user in the session. 

namespace BRMManager\User;
use BRMManager\Permissions AS Permissions;

class Session {
	public $id;
	public $email;
	public $firstname;
	public $lastname;
	public $permissions;
	public $session_timeout;

	// This is created at user login time.
	public function __construct($user_obj, $timeout = "+30 minutes") {
		$this->id = $user_obj->id;
		$this->email = $user_obj->email;
		$this->firstname = $user_obj->firstname;
		$this->lastname = $user_obj->lastname;
		$this->permissions = $user_obj->permissions;

		$this->session_timeout = strtotime($timeout); // Only allow a 30 minute login.
	}

	// This function will keep the current user loggedin on each page view.
	public function keepLoginAlive() {
		// If the user has about 5 mins left before the system logs them off, we need to up it by another 5 minutes.
		$time = time();

		if($this->session_timeout < ($time + (60 * 5)) && $this->session_timeout > $time) {
			// Add five minutes to the current session.
			$this->session_timeout += (60 * 5);
			return TRUE;
		} elseif($this->session_timeout > $time) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function hasAccess($permission_type) {
		return Permissions::hasAccess((int) $this->permissions, $permission_type);
	}
}