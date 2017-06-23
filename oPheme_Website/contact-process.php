<?php

	if (!empty($_POST)) {
		
		session_start();
		
		$ip = GetIP();
		
		$check = check_form_submit($ip);
		
		if ($check['go'] === false) {
			
			$_SESSION['contact_message'] = 'Sorry, you must wait ' . (120 - (time() - strtotime($check['at_time']))) . ' seconds before submitting this request again.';
			header('Location: /contact');
			die ($_SESSION['contact_message']);
			
		}
		
		save_form_submit($ip);
		
		$data = $_POST;
		
		foreach ($data as $value) if (strlen($value) < 1) {
			$_SESSION['contact_message'] = 'Sorry, all form fields must be filled in. Please try again.';
			header('Location: /contact');
			die ('All form fields must be filled in. Redirecting to Contact page...');
		}
		
		$subject = 'Website Contact Form - Message from ' . $data['name'] . ' <' . $data['email'] . '>';

		$ok = send_email('sales@opheme.com', $data['name'], $data['email'], $subject, $data['message'], $ip);
		
		if ($ok === true) {
			
			$_SESSION['contact_ok'] = true;
			header('Location: /contact');
			die ('You have successfully contacted us. Redirecting to Contact page...');
			
		} else {
			
			$_SESSION['contact_message'] = 'Sorry, we have run into some trouble on our end. Please contact us directly at <img src="/img/contact.png" alt="Contact Us" />.';
			header('Location: /contact');
			die ('Failed to complete request. Please report submit a report at http://support.opheme.com if the problem persists. Redirecting to Contact page...');
			
		}
	
	}
	
	header('Location: /contact');
	die ('No input info. Redirecting to Contact page...');

	function send_email($to, $name, $email, $subject, $body, $ip) {
		
		$headers = 'From: ' . $name . ' <' . $email . '>' . "\r\n" .
					'Reply-To: ' . $email . "\r\n" .
					'X-Mailer: PHP/' . phpversion();
		
		$message = filter_var($body, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_NO_ENCODE_QUOTES);
		$message = filter_var($message, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES);
		$message = 'Source IP address: ' . $ip . "\r\n\r\n" . $message;
		
		return mail($to, $subject, $message, $headers);
		
	}
	
	function GetIP() {
		foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
			if (array_key_exists($key, $_SERVER) === true) {
				foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip) {
					if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
						return $ip;
					}
				}
			}
		}
	}
	
	function save_form_submit($ip) {
		
		$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET CHARACTER SET utf8', PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
		$db = null;

		try {
			$db = new PDO("mysql:host=localhost;dbname=opheme_form_submits;charset=utf8", 'opheme', 'oPheme1357!', $options);
		} catch(PDOException $ex) {
			trigger_error($ex);
		}
		
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		
		$query = "INSERT INTO contact_form (user_ip) VALUES (:user_ip)";
		$query_params = array(
			':user_ip' => $ip
		);
		
		try {
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			trigger_error($ex);
		}
		
		if ($stmt->rowCount() === 1) return true;
		
		return false;
		
	}
	
	function check_form_submit($ip) {
		
		$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET CHARACTER SET utf8', PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
		$db = null;

		try {
			$db = new PDO("mysql:host=localhost;dbname=opheme_form_submits;charset=utf8", 'opheme', 'oPheme1357!', $options);
		} catch(PDOException $ex) {
			trigger_error($ex);
		}
		
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		
		$query = "SELECT COUNT(*) as attempts, MAX(submit_time) as last_submit FROM contact_form WHERE user_ip = :user_ip AND submit_time > DATE_SUB(NOW(), INTERVAL 2 MINUTE)";
		$query_params = array(
			':user_ip' => $ip
		);
		
		try {
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			trigger_error($ex);
		}
		
		if ($stmt->rowCount() === 1) {
			
			$row = $stmt->fetch();
			
			if ($row['attempts'] > 0) {
				
				return array('go' => false, 'at_time' => $row['last_submit']);
				
			}
			
			return array('go' => true);
			
		}
		
		//db error
		return array('go' => false);
		
	}

?>