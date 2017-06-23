<?php

//general settings - make sure there are trailing slashes on folder names
$settings = array(
	'jobs_file_directory' => '../../loose-cannon/var/jobs/',
	'jobs_file_name' => 'jobs.txt',
	'jobs_directory' => '../../loose-cannon/var/jobs/',
	'job_files' => array(
		'last_id' => 'lastid.real',
		'latest_id_not_relevant' => 'lastid'
	),
	'job_filter_word_minimum_length' => 3,
	'log_files_directory' => '../../loose-cannon/var/logs/',
	'log_files' => array(
		'error' => 'errors.txt',
		'warning' => 'warnings.txt',
		'notice' => 'notices.txt'
	)
);

?>