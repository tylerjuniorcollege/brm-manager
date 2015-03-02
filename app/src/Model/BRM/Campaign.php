<?php

namespace BRMManager\Model\BRM;

class Campaign
	extends \Model {

	public static $_table = 'brm_campaigns';

	public function versions() {
		return $this->has_many('BRM\ContentVersion', 'brmid');
	}

	public function currentVersion() {
		return $this->has_one('BRM\ContentVersion', 'id', 'current_version')->find_one();
	}

	public function addVersion($content, $userid = NULL, $timestamp = NULL) {
		// If $timestamp is null, then use the created timestamp.
		$version = \Model::factory('BRM\ContentVersion')->create();
		$version->brmid = $this->id;
		$version->content = $content;

		if(is_null($userid)) { // Use CreatedBy
			$userid = $this->createdby;
		}
		$version->userid = $userid;

		if(is_null($timestamp)) {
			$timestamp = $this->created;
		}
		$version->created = $timestamp;
		$version->save();

		$this->current_version = $version->id;
		$this->save();
	}

	public function campaign() {
		return $this->has_one('Campaign', 'id', 'campaignid')->find_one();
	}

	public function addCampaign($name, $desc) {
		$campaign = \Model::factory('Campaign')->create();
		$campaign->name = $name;
		$campaign->description = $desc;
		$campaign->created = $this->created;
		$campaign->createdby = $this->createdby;
		$campaign->save();

		$this->campaignid = $campaign->id;
		$this->save();
	}

	public function authorizeUsers($versionid = NULL) {
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

	public function addRequest() {
		
	}
}