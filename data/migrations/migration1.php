<?php

// This is the comment migration script to facilitate hierarchical comments.

// This is meant to be run by an admin ...


$comments = ORM::for_table('brm_auth_list')->where_not_null('comment')->where_not_equal('comment', '')->find_many();

try {
	ORM::configure('error_mode', PDO::ERRMODE_EXCEPTION);

	ORM::get_db()->beginTransaction();

	// Alter brm_auth Table to include comment id:
	ORM::get_db()->exec('ALTER TABLE `brm_auth_list` ADD `commentid` bigint(20) unsigned NULL AFTER `comment`, ADD FOREIGN KEY (`commentid`) REFERENCES `comments` (`id`);');
	// Alter comments to add a parentid:
	ORM::get_db()->exec('ALTER TABLE `comments` ADD `parentid` bigint(20) unsigned NULL AFTER `comment`, ADD FOREIGN KEY (`parentid`) REFERENCES `comments` (`id`);');

	// Format the current data.
	foreach($comments as $auth_comment) {
		$new_comment = ORM::for_table('comments')->create();

		$new_comment->set(array(
			'userid' => $auth_comment->userid,
			'brmid'  => $auth_comment->brmid,
			'versionid' => $auth_comment->versionid,
			'comment' => $auth_comment->comment,
			'timestamp' => $auth_comment->timestamp
		));

		$new_comment->save();

		$auth_comment->commentid = $new_comment->id;
		$auth_comment->save();

		printf('<p>Added Comment ID:%s from Auth List ID:%s</p>', $new_comment->id, $auth_comment->id);
		echo "\n";
	}

	// Alter table and delete comment/timestamp columns.
	ORM::get_db()->exec('ALTER TABLE `brm_auth_list` DROP `comment`, DROP `timestamp`;');

	ORM::get_db()->commit();
} catch(Exception $e) {
	ORM::get_db()->rollBack();
	echo $e->getMessage() . "\n\n";
	printf('<pre>%s</pre>', $e->getTraceAsString());
}