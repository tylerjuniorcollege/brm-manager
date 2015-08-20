<?php
	// BRMManager - cli application
	// Set current working directory.
	chdir(realpath(__DIR__ . "/../"));

	include('vendor/autoload.php');

	define('DATABASE_FILE', 'data/database.db');

	use League\Flysystem\Filesystem;
	use League\Flysystem\Adapter\Local as Adapter;
	use League\Flysystem\Plugin\ListWith;

	$filesystem = new Filesystem(new Adapter('./data/schema'));
	$filesystem->addPlugin(new ListWith());

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

		// Get latest version file.
		$current_schema = NULL;
		foreach($filesystem->listWith(array('timestamp', 'size')) as $schema) {
			if(!is_null($current_schema) && $schema['timestamp'] < $current_schema['timestamp']) {
				continue;
			}

			$current_schema = $schema;
		}

		// Ask user if the version is ok.
		$resp = $cli->print_read('Installing Version ' . $current_schema['filename'] . ' (y/n)? ');
		if($resp == 'n' || $resp == 'N') {
			die();
		}

		$schema = 'data/schema/'. $current_schema['path'];
		exec('sqlite3 ' . DATABASE_FILE . '< ' . $schema);
	}

	if($cli->opt('createadmin')) {
		// Specify the Database and then add User.
		\ORM::configure('sqlite:'. DATABASE_FILE);

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