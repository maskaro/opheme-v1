#!/usr/bin/php -q
<?php

$php_folder = realpath($_SERVER["argv"][1]);
$jobs_file = realpath($_SERVER["argv"][2]);

require_once($php_folder . '/settings.php');
require_once($php_folder . '/db.class.php');
require_once($php_folder . '/opheme.class.php');

$opheme = new opheme($db_gen);
$text = '';

//Discover jobs
$text .= $opheme->system_discover_getJobs();

//Campaign jobs
$text .= $opheme->system_campaign_getJobs();

//write to file
file_put_contents($jobs_file, $text);

?>