<?php

	require_once("php/common.php");
	require_once("php/protect.php");
	
	if (!empty($_POST)) {
		
		if (empty($_POST['old-password'])) {
			$_SESSION['account_message'] = "In order to make any changes to your account, please provide your current password.";
			header("Location: /account");
            die("Please enter a password. Redirecting to Account ...");
        }
		
		if (strlen($_POST['new-password']) > 0) { //if anything is provided, attempt to validate password
			if ($_POST['new-password'] !== $_POST['confirm-password']) {
				$_SESSION['account_message'] = "Passwords do not match.";
				header("Location: /acount");
				die("Passwords do not match. Redirecting to Account ...");
			}
		}
		
		$ok = $user->change_details($_POST);
		
		if ($ok === true) {
			
			if (strlen($_POST['new-password']) > 0) {
				
				$_SESSION['login_message'] = 'Password has been successfully changed. You have been logged out, please login using your new password.';
				
				header("Location: /logout");
				die ('Password successfully changed. You have been logged out, please login using your new password. Redirecting to Login...');
				
			}
			
			$_SESSION['account_ok'] = true;
			
			header("Location: /account");
			die ('Account info was saved successfully. Redirecting to Account...');
			
		} else {
			
			$_SESSION['account_message'] = 'Failed to complete request, database issue. Please report submit a report at http://support.opheme.com if the problem persists.';
			
			header("Location: /account");
			die ('Failed to complete request, database issue. Please report submit a report at http://support.opheme.com if the problem persists. Redirecting to Account...');
			
		}
	
	}

?>