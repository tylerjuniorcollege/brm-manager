<?php

namespace BRMManager;

class Permissions
{
	private static $permissions = array(
		'view' => 1,
		'approve' => 2,
		'edit' => 4,
		'create' => 8,
		'admin' => 16
	);

	public static function hasAccess($user_perm, $permission) {
		if(is_int($user_perm) && array_key_exists($permission, self::$permissions)) {
			if(($user_perm & self::$permissions[$permission]) !== 0) {
				return TRUE;
			}
		}
		return FALSE;
	}

	public static function userCan($user_perm) {
		$permission = array();
		if(is_int($user_perm)) {
			foreach (self::$permissions as $key => $value) {
				if(self::hasAccess($user_perm, $key) == TRUE) {
					$permission[] = $key;
				}
			}
			return $permission;
		}

		return FALSE;
	}
}