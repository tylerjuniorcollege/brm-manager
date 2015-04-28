<?php

namespace BRMManager\Model\BRM;

class ContentVersion
	extends \Model
{
	public static $_table = 'brm_content_version';

	public function countApproved() {
		$count = $this->has_many('BRM\AuthList', 'versionid')->where('approved', 1)->count();
		return ($count > 0 ? $count : NULL);
	}

	public function countDenied() {
		$count = $this->has_many('BRM\AuthList', 'versionid')->where('approved', -1)->count();
		return ($count > 0 ? $count : NULL);
	}

	public function countAwaiting() {
		$count = $this->has_many('BRM\AuthList', 'versionid')->where('approved', 0)->count();
		return ($count > 0 ? $count : NULL);
	}

	public static function formatDate($orm, $env) {
		switch($env) {
			case 'development':
				return $orm->select('*')->select_expr('strftime("%m-%d-%Y %H:%M:%S", "created", "unixepoch")', 'created');
				break;

			case 'production' :
				return;
				break;
		}
	}
}