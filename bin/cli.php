<?php
	// BRMManager - cli application
	include('../vendor/autoload.php');

	define('DATABASE_FILE', '../data/database.db');

	$cli = new Cli(array(
		'install' => "Creates the database for application",
		'reset' => "Deletes and Recreates the Database when the --install flag is used",
		'createadmin:' => "Creates an admin with the suppled email address",
		'h' => 'This help.'
	));

	$cli->print_help('h', 'BRM Manager Cli Admin', TRUE);

	if($cli->opt('install')) {
		// Check to see if file exists in the filesystem.
		if(is_file(DATABASE_FILE) && !$cli->opt('reset')) {
			$cli->print_exit('Database file exists. Please use --reset if you want to replace this file.');
		} elseif(is_file(DATABASE_FILE) && $cli->opt('reset')) {
			unlink(DATABASE_FILE);
		}

		\ORM::configure('sqlite:'. DATABASE_FILE);

		
	}

	if($cli->opt('createadmin')) {
		// Specify the Database and then add User.
		if(!$cli->opt('install')) {
			\ORM::configure('sqlite:'. DATABASE_FILE);
		}

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