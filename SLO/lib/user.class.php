<?php

class user extends db {
	
	function __construct($_db_cred) {
		
		parent::__construct($_db_cred);
		
	}
	
	function __destruct() {
		
		parent::__destruct();
		
	}
	
	function login($email, $pass) {
		
		$query = "SELECT * FROM users WHERE email = :email";
		$query_params = array(':email' => $email);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		$row = $stmt->fetch();
		
		if($stmt->rowCount() == 1) {
			
			if($this->check_brute($row['email']) == true) { //stop processing logins for account if brute force is attempted
				
				return false;
				
			} else { //check password
	
				$check_password = hash('sha512', $pass . $row['salt']);
				
				for($round = 0; $round < 65536; $round++) {
					$check_password = hash('sha512', $check_password . $row['salt']);
				}
				
				if($check_password === $row['password']) { //all OK
					
					$user_browser = $_SERVER['HTTP_USER_AGENT']; // Get the user-agent string of the user.
					$row['login_string'] = hash('sha512', $row['password'] . $user_browser);
					
					unset($row['password']);
					unset($row['salt']);
					
					return $row;
				
				} else { //record failed login attempt
					
					// Password is not correct
					// We record this attempt in the database
					$now = time();
					
					$query("INSERT INTO login_attempts (email, time) VALUES (:email, :time)");
					$query_params = array(
						'email' => $row['email'],
						'time' => $now
					);
					
					try {
						$stmt = $this->db->prepare($query);
						$result = $stmt->execute($query_params);
					} catch(PDOException $ex) {
						$this->error_message($ex);
					}
					
					return false;
					
				}
				
			}
			
		}
		
		return false; //some db error, $row did not get any values
		
	}
	
	function login_attempts($email) {
		
		$query = "SELECT email FROM login_attempts WHERE email = :email";
		$query_params = array(':email' => $email);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		return $stmt->rowCount();
		
	}
	
	//send email
	private function send_email($to, $subject, $body) {
		
		$from_name = 'oPheme';
		$from_mail = 'noreply@opheme.com';
		$alt_body = 'If you are reading this, please update your email-reading-software. Contact webmaster@opheme.com for support.';
	
		$mail = new PHPMailer(true); //defaults to using php "mail()"; the true param means it will throw exceptions on errors, which we need to catch
	
		try {
			$mail->AddAddress($to, $to);
			$mail->SetFrom($from_mail, $from_name);
			$mail->AddReplyTo($from_mail, $from_name);
			$mail->Subject = $subject;
			$mail->AltBody = $alt_body; // optional - MsgHTML will create an alternate automatically
			$mail->MsgHTML($body);
			$mail->Send();
			return true;
		} catch (phpmailerException $e) {
			return $e->errorMessage(); //Pretty error messages from PHPMailer
		} catch (Exception $e) {
			//echo $e->getMessage(); //Boring error messages from anything else!
			return false;
		}
		
		return true;
		
	}
	
	private function check_brute($email) {
		
		// Get timestamp of current time
		$now = time();
		// All login attempts are counted from the past 2 hours.
		$valid_attempts = $now - (2 * 60 * 60);
		
		$query = 'SELECT time FROM login_attempts WHERE email = :email AND time > :valid_attempts';
		$query_params = array(
			'email' => $email,
			'valid_attempts' => $valid_attempts
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
			
		// If there has been more than 5 failed logins
		if($stmt->rowCount() > 5) {
			return true;
		} else {
			return false;
		}
		
	}
	
	function login_check() {
		
		// Check if all session variables are set
		if(isset($_SESSION['user']['email'], $_SESSION['user']['login_string'])) {
			
			$email = $_SESSION['user']['email'];
			$login_string = $_SESSION['user']['login_string'];
			$user_browser = $_SERVER['HTTP_USER_AGENT']; // Get the user-agent string of the user.
			
			$query = 'SELECT password FROM users WHERE email = :email LIMIT 1';
			$query_params = array('email' => $email);
			
			try {
				$stmt = $this->db->prepare($query);
				$result = $stmt->execute($query_params);
			} catch(PDOException $ex) {
				$this->error_message($ex);
			}
			
			if ($stmt->rowCount() == 1) { // If the user exists
				
				$row = $stmt->fetch();
				
				$login_check = hash('sha512', $row['password'] . $user_browser);
				
				if($login_check === $login_string) {
					// Logged In!!!!
					return true;
				} else {
					// Not logged in
					return false;
				}
					
			} else {
				// Not logged in
				return false;
			}
				
		} else {
			// Not logged in
			return false;
		}
		
	}
	
	function register($email, $password) {
			
		$query = "SELECT 1 FROM users WHERE	email = :email";
		$query_params = array(':email' => $email);
		
		try {
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() > 0) {
			return "This email address is already registered";
		}
		
		$query = "INSERT INTO users (password, salt, email) VALUES (:password, :salt, :email)";
		
		$salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
		$password_hash = hash('sha512', $password . $salt);
		
		for($round = 0; $round < 65536; $round++) {
			$password_hash = hash('sha512', $password . $salt);
		}
		
		$query_params = array(
			':password' => $password_hash,
			':salt' => $salt,
			':email' => $email
		);
		
		try {
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		return $this->register_send_email($email, $password);
		
	}
	
	private function register_send_email($email, $password) {
		
		$subject = 'Welcome to oPheme!';
		$body = 'Username: ' . $email . '<br />Password: ' . $password;
		
		return $this->send_email($email, $subject, $body);
		
	}
	
}