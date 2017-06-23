<?php

    require_once("lib/common.php");
    
    if(!empty($_POST)) {
    
        if(empty($_POST['password'])) {
            die("Please enter a password.");
        }
    
        if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            die("Invalid E-Mail Address");
        }
    
        $register_ok = $user->register($_POST['email'], $_POST['password']);
        
        if ($register_ok === true) {
    
            header("Location: login.php");
            die("Redirecting to login.php");
            
        } elseif (is_string($register_ok)) {
            
            print('<b>' . $register_ok . '</b>.');
            
        } else {
            
            print('Registration OK, but the Confirmation Email failed to send. Please contact webmaster@opheme.com.');
            
        }
    
    }
    
?>
<h1>Register</h1>
<form action="register.php" method="post">
	E-Mail:<br />
	<input type="text" name="email" value="" />
	<br /><br />
	Password:<br />
	<input type="password" name="password" value="" />
	<br /><br />
	<input type="submit" value="Register" />
</form>