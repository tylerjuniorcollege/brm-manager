<?php

namespace BRMManager\Model;

class NotifyTemplates
	extends \Model
{
	public static $_table = 'notifytemplates';

	public static function template($orm, $filter) {
		return $orm->where('name', $filter);
	}

	public function newEmail() { // This will create a new email for this template.

	}
}