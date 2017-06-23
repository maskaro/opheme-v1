<?php

    require_once("php/common.php");
    
    if (!empty($_POST)) {
    
        if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
			$_SESSION['reset_message'] = "Invalid E-Mail Address.";
			header("Location: /login");
            die("Invalid E-Mail Address. Redirecting to Login ...");
        }
    
        $reset_ok = $user->reset_password($_POST['email']);
        
        if (is_string($reset_ok)) {
			
			$_SESSION['reset_message'] = $reset_ok;
			header("Location: /login");
            die("<b>$reset_ok</b>. Redirecting to Login ...");
            
        } elseif ($reset_ok === true) {
			
			$user->removeToken($_POST['token']);
            
			$_SESSION['reset_ok'] = true;
            header("Location: /login");
            die("Password reset OK. Redirecting to Login ...");
            
        } else {
            
			$_SESSION['reset_message'] = "Password reset OK, but the Confirmation Email failed to send. Please report submit a report at http://support.opheme.com. Meanwhile, please use the following password to login: <strong>" . $_SESSION['new_pass'] . "</strong>";
			header("Location: /login");
            die("Password reset OK, but the Confirmation Email failed to send. Please report submit a report at http://support.opheme.com. Redirecting to Login ...");
            
        }
    
    }
	
	header("Location: /login");
    die("No input data. Redirecting to Login ...");
    
?>