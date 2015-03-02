<?php

namespace BRMManager\Model\BRM;

class Campaign
	extends \Model {

	public static $_table = 'brm_campaigns';
	
	public function versions() {
		return $this->has_many('\BRMManager\Model\BRM\ContentVersion', 'brmid');
	}
}