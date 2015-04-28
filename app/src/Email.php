<?php

namespace BRMManager;

// This is a basic email class. It handles the sending and templating for messages.
class Email
	extends \Mandrill
{
	public $template;
	public $msg_arr = array('to' => array(), 'text' => '', 'track_opens' => true);

	public function replace($search, $replace) {
		$this->msg_arr['text'] = str_replace($search, $replace, $this->template);

		return $this;
	}

	public function to($email, $name) {
		$this->msg_arr['to'][] = array(
			'email' => $email,
			'name' => $name,
			'type' => 'to'
		);

		return $this;
	}

	public function from($email, $name) {
		$this->msg_arr['from_email'] = $email;
		$this->msg_arr['from_name'] = $name;
		return $this;
	}

	public function subject($subject) {
		$this->msg_arr['subject'] = $subject;
		return $this;
	}

	public function send() {
		return $this->messages->send($this->msg_arr);
	}

	public function cleanEmail() {
		$this->msg_arr['to'] = array();
	}
}