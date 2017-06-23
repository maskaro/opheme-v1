<?php

if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {

	function undo_magic_quotes_gpc(&$array) {
		foreach($array as &$value) {
			if(is_array($value)) {
				undo_magic_quotes_gpc($value);
			} else {
				$value = stripslashes($value);
			}
		}
	}
	
	undo_magic_quotes_gpc($_POST);
	undo_magic_quotes_gpc($_GET);
	undo_magic_quotes_gpc($_COOKIE);

}

header('Content-Type: text/html; charset=utf-8');

require_once('../config/settings.php');
require_once('session.class.php');
require_once('phpmailer/class.phpmailer.php');
require_once('user.class.php');

$session = new session();
// Set to true if using https
$session->start_session('_oph_sess', false);

$user = new user($db_sec_sess);