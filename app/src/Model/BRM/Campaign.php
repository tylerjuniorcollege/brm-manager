<?php

namespace BRMManager\Model\BRM;
use BRMManager\Model\Campaign as SysCampaigns;
use BRMManager\Model\BRM\ContentVersion as ContentVersion;
use BRMManager\Model\BRM\Request as Request;
use BRMManager\Model\BRM\StateChange as StateChange;

class Campaign
	extends \Model {

	public static $_table = 'brm_campaigns';

	public function versions() {
		return $this->has_many('BRM\ContentVersion', 'brmid')->order_by_desc('id', 'created');
	}

	public function currentVersion() {
		return $this->has_one('BRM\ContentVersion', 'id', 'current_version')->find_one();
	}

	public function addVersion(ContentVersion $version) {
		// If $timestamp is null, then use the created timestamp.
		$version->brmid = $this->id;
		$version->save();

		$this->current_version = $version->id;
		$this->save();
	}

	public function campaign() {
		return $this->has_one('Campaign', 'id', 'campaignid')->find_one();
	}

	public function addCampaign(SysCampaigns $campaign) {
		$this->campaignid = $campaign->id;
	}

	public function authorizedUsers($versionid = NULL) {
		if(is_null($versionid)) {
			$versionid = $this->current_version;
		}
		return $this->has_many('BRM\AuthList', 'brmid')->where('versionid', $versionid);
	}

	public function addUsers($users, $permissions) {
		// Make sure to add the users.
		foreach($users as $user) {
			$authuser = \Model::factory('BRM\AuthList')->create();
			$authuser->userid = $user;
			$authuser->brmid = $this->id;
			$authuser->versionid = $this->current_version;
			$authuser->permission = $permissions[$user];
			$authuser->save();
		}
	}

	public function removeUsers($users) {
		foreach($users as $user) {
			$authuser = \Model::factory('BRM\AuthList')->where(array(
				'brmid' => $this->id,
				'versionid' => $this->current_version,
				'userid' => $user
			))->find_one();

			$authuser->delete();
		}
	}

	public function owner() {
		return $this->has_one('User', 'id', 'createdby')->find_one();
	}

	public function request() {
		return $this->has_one('BRM\Request', 'id', 'requestid')->find_one();
	}

	public function addRequest(Request $request) {
		if(is_null($this->requestid)) {
			$this->requestid = $request->id;
			$this->save();
		}
	}

	public function state() {
		return $this->has_one('BRM\State', 'id', 'stateid')->find_one();
	}

	public function state_change() {
		return $this->has_many('BRM\StateChange', 'brmid');
	}

	public function changeState(StateChange $statechange) {
		$statechange->brmid = $this->id;
		$statechange->versionid = $this->current_version;
		$statechange->save();

		$this->stateid = $statechange->stateid;
		$this->save();
	}

	/** filters **/
	public static function title($orm, $filter) {
		return $orm->where_like('title', '%' . $filter . '%');
	}
	public static function description($orm, $filter) {
		return $orm->where_like('description', '%' . $filter . '%');
	}
	public static function templateid($orm, $filter) {
		return $orm->where_like('templateid', '%' . $filter . '%');
	}

	// See if I can overload the filter and grab the date, based on the applicationenv
	public static function formatDate($orm, $filter) {
		switch($filter) {
			case 'development':
				return $orm->select('*')->select_expr('strftime("%m-%d-%Y %H:%M:%S", "created", "unixepoch")', 'created')->select_expr('strftime("%m-%d-%Y %H:%M:%S", "launchdate", "unixepoch")', 'launchdate');
				break;

			case 'production':
				return; //$orm->select_expr();
				break;
		}
	}
}