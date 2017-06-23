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
	
	require_once(__DIR__.'/../php_fw/phpmailer/class.phpmailer.php');
	
	require_once(__DIR__.'/../config/settings.php');
	
	require_once('session.class.php');
	require_once(__DIR__.'/../php_fw/twitteroauth/twitteroauth.php');
	require_once('db.class.php');
	require_once('user.class.php');
	require_once('opheme.class.php');
	
	$session = new session();
	
	if (isset($_GET['session_id'])) {
		
		if ($session->is_session($_GET['session_id'])) {
			
			function curPageURL() {
				$pageURL = 'http';
				if ($_SERVER["HTTPS"] == "on") { $pageURL .= "s"; }
				$pageURL .= "://";
				if ($_SERVER["SERVER_PORT"] != "80") {
					$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
				} else {
					$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
				}
				return $pageURL;
			}
			
			// Set to true if using https
			$session->start_session('_oph_sess', false, $_GET['session_id']);
			
		}
		
	} else {
	
		// Set to true if using https
		$session->start_session('_oph_sess', false);
		
	}
	
	$user = new user($db_sec_sess);
	$opheme = new opheme($db_gen);
	
	//$opheme->purge_jobs('campaigns'); $opheme->purge_jobs('discovers');
	
	if (isset($_SESSION['user'])){
		if ($opheme->system_twitter_didUserAuthorize()) $_SESSION['twitter_authorized'] = true; else $_SESSION['twitter_authorized'] = false;
		$_SESSION['showCreationModule'] = $user->showCreationModule();
	}
	