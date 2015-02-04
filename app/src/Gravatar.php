<?php

namespace BRMManager\Gravatar;

function genUrl($email) {
	return 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . "?s=64&d=identicon";
}