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
	
	require_once(__DIR__.'/../config/settings.php');
	
	require_once('db.class.php');
	require_once('opheme.class.php');
	
	session_start();
	
	$opheme = new opheme($db_gen);
	
	function get_domain($url) {
		$domain = strtolower($url);
		//more than 1 dot means there's a subdomain there
		if (substr_count($domain, '.') > 1) $domain = substr($domain, strpos($domain, '.') + 1);
		if (substr_count($domain, '.') > 0) $domain = substr($domain, 0, strpos($domain, '.'));
		return $domain;
	}
	
	if (substr_count($_SERVER['SERVER_NAME'], '_') > 0) $company = strtolower(substr($_SERVER['SERVER_NAME'], 0, stripos($_SERVER['SERVER_NAME'], '_')));
	else $company = get_domain($_SERVER['SERVER_NAME']);
	
	$company_files = getcwd() . '/../Rebrands/' . $company;