<?php
	/**
	 * BRM Manager Release Tool.
	 * 
	 * Purpose: To create a minified version of the bower components required for each page.
	 * How to use: For any javascript or CSS files that you want to use in the release, add it to the ./app/config/build.config.php file and array.
	 **/

	// Since this app is meant to be as hands off as possible, this will run after every install and update with composer. This will use whatever is in the configuration file for the page
	// and it will generate a compressed and minified version ready for distribution. ONLY USE FRAMEWORK/BOWER COMPONENT CODE HERE.

	include_once('vendor/autoload.php');

	$cli = new cli(array(
		'dev' => 'This switch will not compress and minify public/js files. (used for Dev)'
	));

	// Load file and start parsing the array.
	$parse_arr = include_once('app/config/build.config.php');
	//$cli->print_dump($parse_arr);

	foreach($parse_arr as $page_loc => $assets) {
		
	}