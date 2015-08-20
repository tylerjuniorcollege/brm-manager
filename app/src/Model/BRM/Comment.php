<?php

namespace BRMManager\Model\BRM;

class Comment
	extends \Model
{
	public static $_table = 'comments';

	public function parent() {
		return $this->belongs_to('BRM\Comment', 'parentid')->find_one();
	}

	public function children() {
		return $this->has_many('BRM\Comment', 'parentid');
	}

	public function brm() {
		return $this->belongs_to('Campaign', 'brmid')->find_one();
	}

	public function version() {
		return $this->belongs_to('BRM\ContentVersion', 'versionid')->find_one();
	}

	public function user() {
		return $this->belongs_to('User', 'userid')->find_one();
	}

	public function authList() {
		return $this->has_one('BRM\AuthList', 'commentid')->find_one();
	}
}