<?php

	require_once("php/common.php");
	
	//remove session stored data
	unset($_SESSION['user']);
	
	// Delete the actual cookie.
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
	
	//destroy session
	session_destroy();
	
	header("Location: /login");
	die("Logged out. Redirecting to Login ...");