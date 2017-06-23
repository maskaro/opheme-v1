<?php

	require('php/common.php');
	
	$redirect_to = '/dashboard';
    $attempts = 0;
	
	if(!empty($_POST)) {
	
		$info = $user->login($_POST['email'], $_POST['password']);
		
		if($info !== false) {
			
			$_SESSION['user'] = $info; //store user DB info in session for later use as needed
			
			header("Location: " . $redirect_to);
			die("Redirecting to: " . $redirect_to);
		
		} else {
			
			$_SESSION['login_attempts'] = $user->login_attempts($_POST['email']);
	
			header("Location: /login");
			die("Login failed. Redirecting to Login ...");
			
		}
		
	}
	
	header("Location: /login");
	die("No data supplied. Redirecting to Login ...");

?>