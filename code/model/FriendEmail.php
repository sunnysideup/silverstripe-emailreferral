<?php

class FriendEmail extends DataObject {

	static $db = array(
		'To' => 'Varchar(200)',
		'Message' => 'Text',
		'From' => 'Varchar(200)',
		'IPAddress' => 'Varchar(50)'
	);

	static $has_one = array(
		'Page' => 'Page'
	);

	static $searchable_fields = array('To', 'Message', 'From', 'IPAddress', 'Page.Title');

	static $summary_fields = array('Created', 'To', 'Message', 'From', 'IPAddress', 'Page.Title');

	static $singular_name = 'Friend Email';

	static $plural_name = 'Friend Emails';

	static $default_sort = 'Created DESC';
}

