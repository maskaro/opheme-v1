<?php

    require_once("php/common.php");
    
    if (!empty($_POST)) {
		
		/*if(empty($_SESSION['captcha_code']) || strcasecmp($_SESSION['captcha_code'], $_POST['captcha_code']) != 0) {
			$_SESSION['register_message'] = "Incorrect Captcha code.";
			header("Location: /login");
			die("Incorrect captcha. Redirecting to Login ...");
		}*/
		
		if (empty($_POST['token'])) {
			$_SESSION['register_message'] = "Please enter your Secret Token.";
			header("Location: /login");
            die("Please enter your secret token. Redirecting to Login ...");
        } else {
			/* PURGE TOKENS BEFORE CHECKING ANYTHING */$user->purgeTokens();
			if (!$user->isToken($_POST['token'], $_POST['email'])) {
				$_SESSION['register_message'] = "Invalid Token. Please enter your Secret Token correctly (check the Token Email you received). Tokens expire after 3 days.";
				header("Location: /login");
				die("Please enter a correct Token. Redirecting to Login ...");
			}
		}
    
        if (empty($_POST['password'])) {
			$_SESSION['register_message'] = "Please enter a password.";
			header("Location: /login");
            die("Please enter a password. Redirecting to Login ...");
        }
		
		if ($_POST['password'] !== $_POST['confirm-password']) {
			$_SESSION['register_message'] = "Passwords do not match.";
			header("Location: /login");
            die("Passwords do not match. Redirecting to Login ...");
        }
		
		if (!isset($_POST['terms'])) {
			$_SESSION['register_message'] = "Please agree to our T&C.";
			header("Location: /login");
			die("Please agree to our T&C. Redirecting to Login ...");
		}
    
        if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
			$_SESSION['register_message'] = "Invalid E-Mail Address.";
			header("Location: /login");
            die("Invalid E-Mail Address. Redirecting to Login ...");
        }
    
        $register_ok = $user->register($_POST['email'], $_POST['password'], $_POST['token']);
        
        if (is_string($register_ok)) {
			
			$_SESSION['register_message'] = $register_ok;
			header("Location: /login");
            die("<b>$register_ok</b>. Redirecting to Login ...");
            
        } elseif ($register_ok === true) {
			
			$user->removeToken($_POST['token']);
            
			$_SESSION['register_ok'] = true;
            header("Location: /login");
            die("Registration OK. Redirecting to Login ...");
            
        } else {
            
			$_SESSION['register_message'] = "Registration OK, but the Confirmation Email failed to send. Please report submit a report at http://support.opheme.com.";
			header("Location: /login");
            die("Registration OK, but the Confirmation Email failed to send. Please report submit a report at http://support.opheme.com. Redirecting to Login ...");
            
        }
    
    }
	
	header("Location: /login");
    die("No input data. Redirecting to Login ...");
    
?>