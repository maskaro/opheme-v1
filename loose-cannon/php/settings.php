<?php

//db settings
$db_gen = array(
	'username' => 'opheme',
	'password' => 'oPheme1357!',
	'host' => 'localhost',
	'dbname' => 'opheme'
);

$db_sec_sess = array(
	'username' => 'sec_user',
	'password' => 'sec1357!',
	'host' => 'localhost',
	'dbname' => 'secure_login'
);

//general settings - make sure there are trailing slashes on folder names
$settings = array(
	'job_filter_word_minimum_length' => 3,
	'log_files_directory' => '../../loose-cannon/var/logs/',
	'log_files' => array(
		'error' => 'errors.txt',
		'warning' => 'warnings.txt',
		'notice' => 'notices.txt'
	)
);

//Twitter
//application credentials
define("CONSUMER_KEY", "ixsSi0R6alETD4hsZjq6YkZA2");
define("CONSUMER_SECRET", "wwCR1Sf1zzsKC0oAbL3J0uT6MAaRScB9vQT6gaMZtSBl8E6x5s");

?>