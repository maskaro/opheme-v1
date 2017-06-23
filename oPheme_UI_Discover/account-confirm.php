<?php

	require_once("php/common.php");
	
	if (!empty($_GET)) {
		
		$ok = $user->register_confirm_account($_GET['code']);
		
		if ($ok == false) {
			$_SESSION['login_message'] = "Invalid code or confirmation email failed to send (in which case you're OK). Please report submit a report at http://support.opheme.com if the problem persists.";
			header("Location: /login");
			die ("Invalid code or confirmation email failed to send (in which case you're OK). Please report submit a report at http://support.opheme.com if the problem persists.");
		}
		
		$_SESSION['confirm_ok'] = true;
		header("Location: /login");
        die("Account confirmed. Redirecting to Login.");
	
	}