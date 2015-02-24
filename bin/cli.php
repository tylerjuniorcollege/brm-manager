<?php
	// BRMManager - cli application
	include('../vendor/autoload.php');

	$cli = new Cli(array(
		'install' => "Creates the database for application",
		'reset' => "Deletes and Recreates the Database when the --install flag is used",
		'createadmin:' => "Creates an admin with the suppled email address",
		'h' => 'This help.'
	));

	$cli->print_help('h', 'BRM Manager Cli Admin', TRUE);

	if($cli->opt('install')) {

	}

	if($cli->opt('createadmin')) {
		// Specify the Database and then add User.

		\ORM::configure('sqlite:../data/database.db');

		$firstname = $cli->print_read('First Name:');
		$lastname = $cli->print_read('Last Name:');

		$newuser = \ORM::for_table('user')->create();
		$newuser->email = $cli->opt('createadmin', TRUE);
		$newuser->firstname = $firstname;
		$newuser->lastname = $lastname;
		$newuser->permissions = 31;
		$newuser->created = time();
		$newuser->save();

		$cli->print_line('User Created: ID#' . $newuser->id);
	}