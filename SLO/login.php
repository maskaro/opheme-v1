<?php

	require('lib/common.php');
	
	$submitted_email = '';
	$redirect_to = '/';
    $attempts = 0;
	
	if(!empty($_POST)) {
	
		$info = $user->login($_POST['email'], $_POST['password']);
		
		if($info !== false) {
			
			$_SESSION['user'] = $info; //store user DB info in session for later use as needed
			
			header("Location: " . $redirect_to);
			die("Redirecting to: " . $redirect_to);
		
		} else {
			
			$attempts = $user->login_attempts();
	
			print("Login Failed.");
			
			$submitted_email = htmlentities($_POST['email'], ENT_QUOTES, 'UTF-8');
			
		}
		
	}

?>
<h1>Login</h1>
<form action="login.php" method="post">
	Email:<br />
	<input type="text" name="email" value="<?php echo $submitted_email; ?>" />
	<br /><br />
	Password:<br />
	<input type="password" name="password" value="" />
	<br /><br />
	Attempts so far: <?php echo $attempts; ?>. <?php if ($attempts == 6) echo 'This account is now locked for 24h.'; ?>
	<br /><br />
	<input type="submit" value="Login" />
</form>
<a href="register.php">Register</a>