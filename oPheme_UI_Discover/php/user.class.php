<?php

class user extends db {
	
	protected $default_sub = 0;
	
	function __construct($_db_cred) {
		
		parent::__construct($_db_cred);
		
	}
	
	function __destruct() {
		
		parent::__destruct();
		
	}
	
	function log($table, $action) {
		
		$query = "INSERT INTO opheme_logs.$table (user_id, action) values (:user_id, :action)";
		$query_params = array(
			':user_id' => $_SESSION['user']['email'],
			':action' => $action
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
	}
	
	function getAllowance() {
		
		$query = "SELECT * FROM opheme.sub_limits WHERE id = :user_sub";
		$query_params = array(':user_sub' => $_SESSION['user']['subscription']);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() == 1) {
			
			$row = $stmt->fetch();
			$message = '';
			
			$message .= 'Type: <strong>' . $row['name'] . '</strong>.<br />';
			
			if (intval($row['account_time_limit']) > 0) {
				$now = time(); $until = strtotime('+' . $row['account_time_limit'], strtotime($_SESSION['user']['created'])); $diff = $until - $now;
				if ($diff < 0) $diff = 0; $left = floor($diff/(60*60*24));
				$message .= 'Account Time Limit: <strong>' . $row['account_time_limit'] . '</strong> (Left: <strong>' . $left . ' Days</strong>).' . ($diff == 0?' <span style="color: red">Trial period has expired! Please consider subscribing to continue enjoying $brand.</span>':'') . ' <br />';
				if ($diff == 0) $_SESSION['trial_expired'] = true; else $_SESSION['trial_expired'] = false;
			} else {
				$_SESSION['trial_expired'] = false;
			}
			if (intval($row['discover_job_limit']) > 0) {
				$limit = $row['discover_job_limit'];
				if (intval($row['discover_job_limit'] == 2147483647)) { $limit = 'Unlimited'; $left = 'A lot'; }
				else { $left_disc = $row['discover_job_limit'] - $this->getDiscoverCount(); }
				$message .= 'Discover Count Limit: <strong>' . $limit . '</strong>
							(Left: <strong>' . $left_disc . '</strong>). <br />';
			}
			if (intval($row['campaign_job_limit']) > 0) {
				$limit = $row['campaign_job_limit'];
				if (intval($row['campaign_job_limit'] == 2147483647)) { $limit = 'Unlimited'; $left = 'A lot'; }
				else { $left_camp = $row['campaign_job_limit'] - $this->getCampaignCount(); }
				$message .= 'Campaign Count Limit: <strong>' . $limit . '</strong>
							(Left: <strong>' . $left_camp . '</strong>). <br />';
			}
			if (intval($row['time_limit']) > 0) $message .= 'Task Time Limit: <strong>' . $row['time_limit'] . '</strong>. <br />';
			if (intval($row['messages_limit']) > 0) {
				$message .= 'Messages Limit per Task: <strong>';
				if (intval($row['messages_limit']) > 99999999999999) $message .= 'Unlimited';
				else $message .= $row['messages_limit'];
				$message .= '</strong>. ';
			}
			
			return $message;
			
		}
		
		return false;
		
	}
	
	function showCreationModule() {
		
		$required = array('firstname', 'lastname', 'phone', 'business_type', 'business_www');
		
		foreach ($required as $value) {
			if (strlen($_SESSION['user'][$value]) < 1) return false;
		}
		
		if ($_SESSION['twitter_authorized'] == false) return false;
		
		//check account time limit
		$allowance = $this->getAllowanceArray();
		if (intval($allowance['account_time_limit']) > 0) {
			$until = strtotime('+' . $allowance['account_time_limit'], strtotime($_SESSION['user']['created'])); $now = strtotime('now');
			if ($now >= $until) return false;
		}
		
		return true;
		
	}
	
	function getAllowanceArray() {
		
		$query = "SELECT * FROM opheme.sub_limits WHERE id = :user_sub";
		$query_params = array(':user_sub' => $_SESSION['user']['subscription']);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() == 1) {
			
			$row = $stmt->fetch();
			return $row;
			
		}
		
		return false;
	
	}
	
	function getDiscoverCount() {
		
		$query = "SELECT count(*) as count FROM opheme.discovers WHERE user_id = :email";
		$query_params = array(':email' => $_SESSION['user']['email']);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() == 1) {
			
			$row = $stmt->fetch();
			return $row['count'];
			
		}
		
		return 0;
		
	}
	
	function getCampaignCount() {
		
		$query = "SELECT count(*) as count FROM opheme.campaigns WHERE user_id = :email";
		$query_params = array(':email' => $_SESSION['user']['email']);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() == 1) {
			
			$row = $stmt->fetch();
			return $row['count'];
			
		}
		
		return 0;
		
	}
	
	function getTwitterFollowingCount() {
		
		$query = "SELECT count(*) as count FROM opheme_twitter_follows.follow_forward WHERE opheme_user_id = :email";
		$query_params = array(':email' => $_SESSION['user']['email']);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() == 1) {
			
			$row = $stmt->fetch();
			return $row['count'];
			
		}
		
		return 0;
		
	}
	
	function update_last_login($email) {
		
		$query = "UPDATE users SET last_login = NOW() WHERE email = :email";
		$query_params = array(':email' => $email);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		return $stmt->rowCount();
		
	}
	
	function setFirstLogin($email) {
		
		$query = "UPDATE users SET code = 0 WHERE email = :email";
		$query_params = array(':email' => $email);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		return $stmt->rowCount();
		
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
			
			if (intval($row['suspended']) === 1) {
				
				$_SESSION['login_message'] = "Account has been suspended. For more information please contact us.";
				
				return false;
				
			} else if($this->check_brute($row['email']) == true) { //stop processing logins for account if brute force is attempted
				
				$_SESSION['login_message'] = "Too many logins. Please try again in 2 hours.";
				
				return false;
				
			} else {
				
				if ($row['code'] != '0') { // first login
					
					$this->setFirstLogin($email);
					$this->removeToken($pass);
					
				}
				
				//check password

				$check_password = hash('sha512', $pass . $row['salt']);
				
				for($round = 0; $round < 65536; $round++) {
					$check_password = hash('sha512', $check_password . $row['salt']);
				}
				
				if($check_password === $row['password']) { //all OK
					
					$this->update_last_login($email);
					
					$user_browser = $_SERVER['HTTP_USER_AGENT']; // Get the user-agent string of the user.
					$row['login_string'] = hash('sha512', $row['password'] . $user_browser);
					
					unset($row['password']);
					unset($row['salt']);
					
					return $row;
				
				} else { //record failed login attempt
					
					// Password is not correct
					// We record this attempt in the database
					$now = time();
					
					$query = "INSERT INTO login_attempts (email, time) VALUES (:email, :time)";
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
					
					$_SESSION['login_message'] = "Invalid or incorrect email or password.";
					
					return false;
					
				}
			
			}
		
		}
		
		$_SESSION['login_message'] = "Invalid or incorrect email or password.";
		
		return false;
		
	}
	
	function login_attempts($email) {
		
		// Get timestamp of current time
		$now = time();
		// All login attempts are counted from the past 2 hours.
		$valid_attempts = $now - (2 * 60 * 60);
		
		$query = "SELECT 1 FROM login_attempts WHERE email = :email AND time > :valid_attempts";
		$query_params = array(
			':email' => $email,
			':valid_attempts' => $valid_attempts
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		return $stmt->rowCount();
		
	}
	
	private function check_brute($email) {
		
		// Get timestamp of current time
		$now = time();
		// All login attempts are counted from the past 2 hours.
		$valid_attempts = $now - (2 * 60 * 60);
		
		$query = 'SELECT 1 FROM login_attempts WHERE email = :email AND time > :valid_attempts';
		$query_params = array(
			':email' => $email,
			':valid_attempts' => $valid_attempts
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
	
	function isToken($token, $email = '') {
		
		$query = "SELECT 1 FROM tokens WHERE token = :token AND email = :email";
		$query_params = array(
			':token' => $token,
			':email' => $email
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if ($stmt->rowCount() == 1) return true;
		
		return false;
		
	}
	
	function getTokenCompanyEmail($token) {
		
		$query = "SELECT from_company FROM tokens WHERE token = :token";
		$query_params = array(
			':token' => $token
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if ($stmt->rowCount() == 1) {
			
			$row = $stmt->fetch();
			
			return $row['from_company'];
		
		}
		
		return false;
		
	}
	
	function removeToken($token) {
		
		$query = "DELETE FROM tokens WHERE token = :token";
		$query_params = array(
			':token' => $token
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if ($stmt->rowCount() == 1) return true;
		
		return false;
		
	}
	
	//removes tokens older than 3 days
	function purgeTokens() {
		
		$query = "DELETE FROM tokens WHERE added < DATE_ADD(NOW(), INTERVAL -3 DAY)";
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute();
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		return $result;
		
	}
	
	function system_admin_removeToken($id) {
		
		$query = "DELETE FROM tokens WHERE id = :id";
		$query_params = array(
			':id' => $id
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if ($stmt->rowCount() == 1) {
			$_SESSION['admin_ok'] = 'Successfully deleted token ID ' . $id . '.';
			$this->log('admin_operations', $_SESSION['admin_ok']);
			return true;
		}
		
		$_SESSION['admin_message'] = 'Failed to delete token ID ' . $id . '. Database error. Please submit a report at http://support.opheme.com if this error persists.';
		$this->log('admin_operations_fails', $_SESSION['admin_message']);
		return false;
		
	}
	
	function generate_password($password) {
		$salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
		$password_hash = hash('sha512', $password . $salt);
		
		for($round = 0; $round < 65536; $round++) {
			$password_hash = hash('sha512', $password_hash . $salt);
		}
		
		return $password_hash;
	}
	
	function system_admin_createToken($email) {
		
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$_SESSION['admin_message'] = 'Please provide a valid for sending the Registration Token to.';
			$this->log('admin_operations_fails', $_SESSION['admin_message']);
			return false;
		}
		
		$token = uniqid('', true);
		
		$query = "INSERT INTO tokens (token, email, from_company) VALUES (:token, :email, :from)";
		$query_params = array(
			':token' => $token,
			':email' => $email,
			':from' => $_SESSION['user']['email']
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if ($stmt->rowCount() == 1) {
			$_SESSION['admin_ok'] = 'Successfully created new account.';
			$this->log('admin_operations', $_SESSION['admin_ok']);
			return $this->register($email, $token);
			//return $this->system_admin_sendTokenEmail($email, $token);
		}
		
		$_SESSION['admin_message'] = 'Failed to create new account. Database error. Please submit a report at http://support.opheme.com if this error persists.';
		$this->log('admin_operations_fails', $_SESSION['admin_message']);
		return false;
		
	}
	
	function reset_password($email) {
			
		$query = "SELECT 1 FROM users WHERE	email = :email";
		$query_params = array(':email' => $email);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() == 0) {
			return "This email address you supplied does not exist on the system.";
		}
		
		$query = "UPDATE users SET password = :password, salt = :salt WHERE email = :email";
		
		$salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
		$password = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
		$password_hash = hash('sha512', $password . $salt);
		
		for($round = 0; $round < 65536; $round++) {
			$password_hash = hash('sha512', $password_hash . $salt);
		}
		
		$query_params = array(
			':password' => $password_hash,
			':salt' => $salt,
			':email' => $email
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		return $this->reset_password_send_email($email, $password);
		
	}
	
	private function reset_password_send_email($email, $password) {
		
		$year = current_year;
		$brand = brand;
		$company = company;
		$domain = domain;
		
		$web = 'http://discover.' . $domain . '/login';
		
		$subject = 'Password reset confirmation.';
		$body = <<<OPH
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml"><head>
		        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		        <title>Welcome to $brand</title>        
				<!--[if gte mso 6]>
					<style>
					    table.mcnFollowContent {width:100% !important;}
					    table.mcnShareContent {width:100% !important;}
					</style>
				<![endif]-->
				<style type="text/css">
					#outlook a{
						padding:0;
					}
					.ReadMsgBody{
						width:100%;
					}
					.ExternalClass{
						width:100%;
					}
					body{
						margin:0;
						padding:0;
					}
					a{
						word-wrap:break-word !important;
					}
					img{
						border:0;
						height:auto !important;
						line-height:100%;
						outline:none;
						text-decoration:none;
					}
					table,td{
						border-collapse:collapse;
						mso-table-lspace:0pt;
						mso-table-rspace:0pt;
					}
					#bodyTable,#bodyCell{
						height:100% !important;
						margin:0;
						padding:0;
						width:100% !important;
					}
					#bodyCell{
						padding:20px;
					}
					.mcnImage{
						vertical-align:bottom;
					}
					.mcnTextContent img{
						height:auto !important;
					}
					body,#bodyTable{
						background-color:#ffffff;
					}
					#bodyCell{
						border-top:0;
					}
					#templateContainer{
						border:10px solid #00b1f5;
					}
					h1{
						color:#606060 !important;
						display:block;
						font-family:Helvetica;
						font-size:40px;
						font-style:normal;
						font-weight:bold;
						line-height:125%;
						letter-spacing:-1px;
						margin:0;
						text-align:left;
					}
					h2{
						color:#404040 !important;
						display:block;
						font-family:Helvetica;
						font-size:26px;
						font-style:normal;
						font-weight:bold;
						line-height:125%;
						letter-spacing:-.75px;
						margin:0;
						text-align:left;
					}
					h3{
						color:#606060 !important;
						display:block;
						font-family:Helvetica;
						font-size:18px;
						font-style:normal;
						font-weight:bold;
						line-height:125%;
						letter-spacing:-.5px;
						margin:0;
						text-align:left;
					}
					h4{
						color:#808080 !important;
						display:block;
						font-family:Helvetica;
						font-size:16px;
						font-style:normal;
						font-weight:bold;
						line-height:125%;
						letter-spacing:normal;
						margin:0;
						text-align:left;
					}
					h1 a,h2 a,h3 a,h4 a{
						color:#6DC6DD;
						font-weight:bold;
						text-decoration:none;
					}
					#templatePreheader{
						background-color:#00b1f5;
						border-top:0;
						border-bottom:0;
					}
					.preheaderContainer .mcnTextContent,.preheaderContainer .mcnTextContent p{
						color:#ffffff;
						font-family:Helvetica;
						font-size:11px;
						line-height:125%;
						text-align:left;
					}
					.preheaderContainer .mcnTextContent a{
						color:#ffffff;
						font-weight:normal;
						text-decoration:underline;
					}
					#templateHeader{
						background-color:#00b1f5;
						border-top:0;
						border-bottom:0;
					}
					.headerContainer .mcnTextContent,.headerContainer .mcnTextContent p{
						color:#ffffff;
						font-family:Helvetica;
						font-size:15px;
						line-height:150%;
						text-align:left;
					}
					.headerContainer .mcnTextContent a{
						color:#6DC6DD;
						font-weight:normal;
						text-decoration:underline;
					}
					#templateBody{
						background-color:#ffffff;
						border-top:0;
						border-bottom:0;
					}
					.bodyContainer .mcnTextContent,.bodyContainer .mcnTextContent p{
						color:#606060;
						font-family:Helvetica;
						font-size:15px;
						line-height:150%;
						text-align:left;
					}
					.bodyContainer .mcnTextContent a{
						color:#606060;
						font-weight:normal;
						text-decoration:underline;
					}
					#templateFooter{
						background-color:#00b1f5;
						border-top:0;
						border-bottom:0;
					}
					.footerContainer .mcnTextContent,.footerContainer .mcnTextContent p{
						color:#ffffff;
						font-family:Helvetica;
						font-size:11px;
						line-height:125%;
						text-align:left;
					}
					.footerContainer .mcnTextContent a{
						color:#ffffff;
						font-weight:normal;
						text-decoration:underline;
					}
					@media only screen and (max-width: 480px){
						body,table,td,p,a,li,blockquote{
							-webkit-text-size-adjust:none !important;
						}
					}
					@media only screen and (max-width: 480px){
						body{
							width:100% !important;
							min-width:100% !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[id=bodyCell]{
							padding:10px !important;
						}
					}
					@media only screen and (max-width: 480px){
						table[class=mcnTextContentContainer]{
							width:100% !important;
						}
					}
					@media only screen and (max-width: 480px){
						table[class=mcnBoxedTextContentContainer]{
							width:100% !important;
						}
					}
					@media only screen and (max-width: 480px){
						table[class=mcpreview-image-uploader]{
							width:100% !important;
							display:none !important;
						}
					}
					@media only screen and (max-width: 480px){
						img[class=mcnImage]{
							width:100% !important;
						}
					}
					@media only screen and (max-width: 480px){
						table[class=mcnImageGroupContentContainer]{
							width:100% !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=mcnImageGroupContent]{
							padding:9px !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=mcnImageGroupBlockInner]{
							padding-bottom:0 !important;
							padding-top:0 !important;
						}
					}
					@media only screen and (max-width: 480px){
						tbody[class=mcnImageGroupBlockOuter]{
							padding-bottom:9px !important;
							padding-top:9px !important;
						}
					}
					@media only screen and (max-width: 480px){
						table[class=mcnCaptionTopContent],table[class=mcnCaptionBottomContent]{
							width:100% !important;
						}
					}
					@media only screen and (max-width: 480px){
						table[class=mcnCaptionLeftTextContentContainer],table[class=mcnCaptionRightTextContentContainer],table[class=mcnCaptionLeftImageContentContainer],table[class=mcnCaptionRightImageContentContainer],table[class=mcnImageCardLeftTextContentContainer],table[class=mcnImageCardRightTextContentContainer]{
							width:100% !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=mcnImageCardLeftImageContent],td[class=mcnImageCardRightImageContent]{
							padding-right:18px !important;
							padding-left:18px !important;
							padding-bottom:0 !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=mcnImageCardBottomImageContent]{
							padding-bottom:9px !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=mcnImageCardTopImageContent]{
							padding-top:18px !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=mcnImageCardLeftImageContent],td[class=mcnImageCardRightImageContent]{
							padding-right:18px !important;
							padding-left:18px !important;
							padding-bottom:0 !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=mcnImageCardBottomImageContent]{
							padding-bottom:9px !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=mcnImageCardTopImageContent]{
							padding-top:18px !important;
						}
					}
					@media only screen and (max-width: 480px){
						table[class=mcnCaptionLeftContentOuter] td[class=mcnTextContent],table[class=mcnCaptionRightContentOuter] td[class=mcnTextContent]{
							padding-top:9px !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=mcnCaptionBlockInner] table[class=mcnCaptionTopContent]:last-child td[class=mcnTextContent]{
							padding-top:18px !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=mcnBoxedTextContentColumn]{
							padding-left:18px !important;
							padding-right:18px !important;
						}
					}
					@media only screen and (max-width: 480px){
						table[id=templateContainer],table[id=templatePreheader],table[id=templateHeader],table[id=templateBody],table[id=templateFooter]{
							max-width:600px !important;
							width:100% !important;
						}
					}
					@media only screen and (max-width: 480px){
						h1{
							font-size:24px !important;
							line-height:125% !important;
						}
					}
					@media only screen and (max-width: 480px){
						h2{
							font-size:20px !important;
							line-height:125% !important;
						}
					}
					@media only screen and (max-width: 480px){
						h3{
							font-size:18px !important;
							line-height:125% !important;
						}
					}
					@media only screen and (max-width: 480px){
						h4{
							font-size:16px !important;
							line-height:125% !important;
						}
					}
					@media only screen and (max-width: 480px){
						table[class=mcnBoxedTextContentContainer] td[class=mcnTextContent]{
							font-size:18px !important;
							line-height:125% !important;
						}
					}
					@media only screen and (max-width: 480px){
						table[id=templatePreheader]{
							display:block !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=preheaderContainer] td[class=mcnTextContent]{
							font-size:14px !important;
							line-height:115% !important;
							padding-right:18px !important;
							padding-left:18px !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=headerContainer] td[class=mcnTextContent]{
							font-size:18px !important;
							line-height:125% !important;
							padding-right:18px !important;
							padding-left:18px !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=bodyContainer] td[class=mcnTextContent]{
							font-size:18px !important;
							line-height:125% !important;
							padding-right:18px !important;
							padding-left:18px !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=footerContainer] td[class=mcnTextContent]{
							font-size:14px !important;
							line-height:115% !important;
							padding-right:18px !important;
							padding-left:18px !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=footerContainer] a[class=utilityLink]{
							display:block !important;
						}
					}
				</style>
			</head>
			<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0" style="margin: 0;padding: 0;background-color: #ffffff;">
				<center>
					<table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;margin: 0;padding: 0;background-color: #ffffff;height: 100% !important;width: 100% !important;">
						<tbody>
							<tr>
								<td align="center" valign="top" id="bodyCell" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;margin: 0;padding: 20px;border-top: 0;height: 100% !important;width: 100% !important;">
									<!-- BEGIN TEMPLATE // -->
									<table border="0" cellpadding="0" cellspacing="0" width="600" id="templateContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;border: 10px solid #00b1f5;">
										<tbody>
											<tr>
												<td align="center" valign="top" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
													<!-- BEGIN PREHEADER // -->
													<table border="0" cellpadding="0" cellspacing="0" width="600" id="templatePreheader" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;background-color: #00b1f5;border-top: 0;border-bottom: 0;">
														<tbody>
															<tr>
																<td valign="top" class="preheaderContainer" style="padding-top: 9px;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;"><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																	<table align="left" border="0" cellpadding="0" cellspacing="0" width="450" class="mcnTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																		<tbody class="mcnTextBlockOuter">
																			<tr>
																				<td valign="top" class="mcnTextBlockInner" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																					<table align="left" border="0" cellpadding="0" cellspacing="0" width="450" class="mcnTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																						<tbody>
																							<tr>
																								<td valign="top" class="mcnTextContent" style="padding-top: 9px;padding-left: 18px;padding-bottom: 9px;padding-right: 0;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;color: #ffffff;font-family: Helvetica;font-size: 11px;line-height: 125%;text-align: left;">
																									Password reset confirmation of your $domain Account.
																								</td>
																							</tr>
																						</tbody>
																					</table>
																				</td>
																			</tr>
																		</tbody>
																	</table>
																</td>
															</tr>
														</tbody>
													</table>
													<!-- // END PREHEADER -->
												</td>
											</tr>
											<tr>
												<td align="center" valign="top" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
													<!-- BEGIN HEADER // -->
													<table border="0" cellpadding="0" cellspacing="0" width="600" id="templateHeader" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;background-color: #00b1f5;border-top: 0;border-bottom: 0;">
														<tbody>
															<tr>
																<td valign="top" class="headerContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;"><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnImageBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																	<table align="left" width="100%" border="0" cellpadding="0" cellspacing="0" class="mcnImageContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																		<tbody class="mcnImageBlockOuter">
																			<tr>
																				<td valign="top" style="padding: 9px;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;" class="mcnImageBlockInner">
																					<table align="left" width="100%" border="0" cellpadding="0" cellspacing="0" class="mcnImageContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																						<tbody>
																							<tr>
																								<td class="mcnImageContent" valign="top" style="padding-right: 9px;padding-left: 9px;padding-top: 0;padding-bottom: 0;text-align: center;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">    
																									<img align="center" alt="" src="https://gallery.mailchimp.com/0158f037752d173562593f2ba/images/header.2.png" width="564" style="max-width: 600px;padding-bottom: 0;display: inline !important;vertical-align: bottom;border: 0;line-height: 100%;outline: none;text-decoration: none;height: auto !important;" class="mcnImage">
																								</td>
																							</tr>
																						</tbody>
																					</table>
																				</td>
																			</tr>
																		</tbody>
																	</table>
																</td>
															</tr>
														</tbody>
													</table>
													<!-- // END HEADER -->
												</td>
											</tr>
											<tr>
												<td align="center" valign="top" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
													<!-- BEGIN BODY // -->
													<table border="0" cellpadding="0" cellspacing="0" width="600" id="templateBody" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;background-color: #ffffff;border-top: 0;border-bottom: 0;">
														<tbody>
															<tr>
																<td valign="top" class="bodyContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;"><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																	<table align="left" border="0" cellpadding="0" cellspacing="0" width="600" class="mcnTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																		<tbody class="mcnTextBlockOuter">
																			<tr>
																				<td valign="top" class="mcnTextBlockInner" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">    
																					<table align="left" border="0" cellpadding="0" cellspacing="0" width="600" class="mcnTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																						<tbody>
																							<tr>
																								<td valign="top" class="mcnTextContent" style="padding: 9px 18px;color: #FFFFFF;text-align: center;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;font-family: Helvetica;font-size: 15px;line-height: 150%;">
																									<br>
																									<p style="color: #606060;font-family: Helvetica;font-size: 15px;line-height: 150%;text-align: left;">
																										Thank you bringing your account back online - this really makes our day!<br>
																										&nbsp;
																									</p>
																									<p style="color: #606060;font-family: Helvetica;font-size: 15px;line-height: 150%;text-align: left;">
																										To access your account, please login using the following details at <a href="$web" target="_blank">$web</a>:
																										<br>
																										<br>
																										Username: $email
																										<br>
																										New Password: <strong>$password</strong>
																										<br>
																										<br>
																										<br>
																										Thanks
																										<br>
																										<br>
																										The $brand Team
																									</p>
																								</td>
																							</tr>
																						</tbody>
																					</table>
																				</td>
																			</tr>
																		</tbody>
																	</table>
																</td>
															</tr>
														</tbody>
													</table>
													<!-- // END BODY -->
												</td>
											</tr>
											<tr>
												<td align="center" valign="top" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
													<!-- BEGIN FOOTER // -->
													<table border="0" cellpadding="0" cellspacing="0" width="600" id="templateFooter" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;background-color: #00b1f5;border-top: 0;border-bottom: 0;">
														<tbody>
															<tr>
																<td valign="top" class="footerContainer" style="padding-bottom: 9px;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;"><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																	<table align="left" border="0" cellpadding="0" cellspacing="0" width="600" class="mcnTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																		<tbody class="mcnTextBlockOuter">
																			<tr>
																				<td valign="top" class="mcnTextBlockInner" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">    
																					<table align="left" border="0" cellpadding="0" cellspacing="0" width="600" class="mcnTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																						<tbody>
																							<tr>
																								<td valign="top" class="mcnTextContent" style="padding-top: 9px;padding-right: 18px;padding-bottom: 9px;padding-left: 18px;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;color: #ffffff;font-family: Helvetica;font-size: 11px;line-height: 125%;text-align: left;">
																									<em>Copyright © $year $company, All rights reserved.</em>
																									<br>
																									You are receiving this email because you created an account at www.$domain.
																									<br>
																									<br>
																									<!--<strong>Our mailing address is:</strong><br>		
																									<div class="vcard">
																										<span class="org fn">$company</span>
																										<div class="adr">
																											<div class="street-address">c/o 	Ashcroft Anthony, Heydon Lodge,</div>
																											<div class="extended-address">Flint Cross,  Newmarket Rd,</div>
																											<span class="locality">Heydon</span>, <span class="region">Hertfordshire</span>  <span class="postal-code">SG8 7PN</span> <div class="country-name">United Kingdom</div>
																											<br><a href="http://opheme.us7.list-manage1.com/vcard?u=0158f037752d173562593f2ba&amp;id=a0440c450c" class="hcard-download">Add us to your address book</a>
																										</div>
																										<br>
																									</div>-->
																								</td>
																							</tr>
																						</tbody>
																					</table>
																				</td>
																			</tr>
																		</tbody>
																	</table>
																</td>
															</tr>
														</tbody>
													</table>
													<!-- // END FOOTER -->
												</td>
											</tr>
										</tbody>
									</table>
									<!-- // END TEMPLATE -->
								</td>
							</tr>
						</tbody>
					</table>
				</center>
			</body>
		</html>
OPH;
		
		$plaintext = 'Thank you bringing your account back online - this really makes our day!' . PHP_EOL . PHP_EOL;
		$plaintext .= 'To access your account, please login using the following details at ' . $web . ':' . PHP_EOL . PHP_EOL;
		$plaintext .= 'Username: ' . $email . PHP_EOL;
		$plaintext .= 'New Password: ' . $password . ' (you can change it once logged in)' . PHP_EOL . PHP_EOL;
		$plaintext .= 'Thanks' . PHP_EOL;
		$plaintext .= 'The ' . $brand . ' Team' . PHP_EOL;
		
		$ok = $this->send_email($email, $subject, $body, $plaintext);
		
		if ($ok === true) return true;
		else {
			$_SESSION['new_pass'] = $password;
			return false;
		}
		
	}
	
	function register($email, $password) {
			
		$query = "SELECT 1 FROM users WHERE	email = :email";
		$query_params = array(':email' => $email);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() > 0) {
			return "This email address is already registered.";
		}
		
		$query = "INSERT INTO users (password, salt, email, subscription, code, from_company) VALUES (:password, :salt, :email, :subscription, :code, :from_company)";
		
		$salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
		$password_hash = hash('sha512', $password . $salt);
		
		for($round = 0; $round < 65536; $round++) {
			$password_hash = hash('sha512', $password_hash . $salt);
		}
		
		$query_params = array(
			':password' => $password_hash,
			':salt' => $salt,
			':email' => $email,
			':subscription' => $this->default_sub,
			':code' => 12345,
			':from_company' => $this->getTokenCompanyEmail($password)
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		return $this->register_send_confirm_email($email, $password);
		
	}
	
	private function register_send_confirm_email($email, $password) {
		
		//if emails are not enabled, stop here
		if (enable_emails == false) return true;
		
		$year = current_year;
		$brand = brand;
		$company = company;
		$domain = domain;
		
		$web = 'http://discover.' . $domain . '/login';
		
		$subject = 'Welcome to ' . $brand . '!';
		$body = <<<OPH
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml"><head>
		        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		        <title>Welcome to $brand</title>        
				<!--[if gte mso 6]>
					<style>
					    table.mcnFollowContent {width:100% !important;}
					    table.mcnShareContent {width:100% !important;}
					</style>
				<![endif]-->
				<style type="text/css">
					#outlook a{
						padding:0;
					}
					.ReadMsgBody{
						width:100%;
					}
					.ExternalClass{
						width:100%;
					}
					body{
						margin:0;
						padding:0;
					}
					a{
						word-wrap:break-word !important;
					}
					img{
						border:0;
						height:auto !important;
						line-height:100%;
						outline:none;
						text-decoration:none;
					}
					table,td{
						border-collapse:collapse;
						mso-table-lspace:0pt;
						mso-table-rspace:0pt;
					}
					#bodyTable,#bodyCell{
						height:100% !important;
						margin:0;
						padding:0;
						width:100% !important;
					}
					#bodyCell{
						padding:20px;
					}
					.mcnImage{
						vertical-align:bottom;
					}
					.mcnTextContent img{
						height:auto !important;
					}
					body,#bodyTable{
						background-color:#ffffff;
					}
					#bodyCell{
						border-top:0;
					}
					#templateContainer{
						border:10px solid #00b1f5;
					}
					h1{
						color:#606060 !important;
						display:block;
						font-family:Helvetica;
						font-size:40px;
						font-style:normal;
						font-weight:bold;
						line-height:125%;
						letter-spacing:-1px;
						margin:0;
						text-align:left;
					}
					h2{
						color:#404040 !important;
						display:block;
						font-family:Helvetica;
						font-size:26px;
						font-style:normal;
						font-weight:bold;
						line-height:125%;
						letter-spacing:-.75px;
						margin:0;
						text-align:left;
					}
					h3{
						color:#606060 !important;
						display:block;
						font-family:Helvetica;
						font-size:18px;
						font-style:normal;
						font-weight:bold;
						line-height:125%;
						letter-spacing:-.5px;
						margin:0;
						text-align:left;
					}
					h4{
						color:#808080 !important;
						display:block;
						font-family:Helvetica;
						font-size:16px;
						font-style:normal;
						font-weight:bold;
						line-height:125%;
						letter-spacing:normal;
						margin:0;
						text-align:left;
					}
					h1 a,h2 a,h3 a,h4 a{
						color:#6DC6DD;
						font-weight:bold;
						text-decoration:none;
					}
					#templatePreheader{
						background-color:#00b1f5;
						border-top:0;
						border-bottom:0;
					}
					.preheaderContainer .mcnTextContent,.preheaderContainer .mcnTextContent p{
						color:#ffffff;
						font-family:Helvetica;
						font-size:11px;
						line-height:125%;
						text-align:left;
					}
					.preheaderContainer .mcnTextContent a{
						color:#ffffff;
						font-weight:normal;
						text-decoration:underline;
					}
					#templateHeader{
						background-color:#00b1f5;
						border-top:0;
						border-bottom:0;
					}
					.headerContainer .mcnTextContent,.headerContainer .mcnTextContent p{
						color:#ffffff;
						font-family:Helvetica;
						font-size:15px;
						line-height:150%;
						text-align:left;
					}
					.headerContainer .mcnTextContent a{
						color:#6DC6DD;
						font-weight:normal;
						text-decoration:underline;
					}
					#templateBody{
						background-color:#ffffff;
						border-top:0;
						border-bottom:0;
					}
					.bodyContainer .mcnTextContent,.bodyContainer .mcnTextContent p{
						color:#606060;
						font-family:Helvetica;
						font-size:15px;
						line-height:150%;
						text-align:left;
					}
					.bodyContainer .mcnTextContent a{
						color:#606060;
						font-weight:normal;
						text-decoration:underline;
					}
					#templateFooter{
						background-color:#00b1f5;
						border-top:0;
						border-bottom:0;
					}
					.footerContainer .mcnTextContent,.footerContainer .mcnTextContent p{
						color:#ffffff;
						font-family:Helvetica;
						font-size:11px;
						line-height:125%;
						text-align:left;
					}
					.footerContainer .mcnTextContent a{
						color:#ffffff;
						font-weight:normal;
						text-decoration:underline;
					}
					@media only screen and (max-width: 480px){
						body,table,td,p,a,li,blockquote{
							-webkit-text-size-adjust:none !important;
						}
					}
					@media only screen and (max-width: 480px){
						body{
							width:100% !important;
							min-width:100% !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[id=bodyCell]{
							padding:10px !important;
						}
					}
					@media only screen and (max-width: 480px){
						table[class=mcnTextContentContainer]{
							width:100% !important;
						}
					}
					@media only screen and (max-width: 480px){
						table[class=mcnBoxedTextContentContainer]{
							width:100% !important;
						}
					}
					@media only screen and (max-width: 480px){
						table[class=mcpreview-image-uploader]{
							width:100% !important;
							display:none !important;
						}
					}
					@media only screen and (max-width: 480px){
						img[class=mcnImage]{
							width:100% !important;
						}
					}
					@media only screen and (max-width: 480px){
						table[class=mcnImageGroupContentContainer]{
							width:100% !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=mcnImageGroupContent]{
							padding:9px !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=mcnImageGroupBlockInner]{
							padding-bottom:0 !important;
							padding-top:0 !important;
						}
					}
					@media only screen and (max-width: 480px){
						tbody[class=mcnImageGroupBlockOuter]{
							padding-bottom:9px !important;
							padding-top:9px !important;
						}
					}
					@media only screen and (max-width: 480px){
						table[class=mcnCaptionTopContent],table[class=mcnCaptionBottomContent]{
							width:100% !important;
						}
					}
					@media only screen and (max-width: 480px){
						table[class=mcnCaptionLeftTextContentContainer],table[class=mcnCaptionRightTextContentContainer],table[class=mcnCaptionLeftImageContentContainer],table[class=mcnCaptionRightImageContentContainer],table[class=mcnImageCardLeftTextContentContainer],table[class=mcnImageCardRightTextContentContainer]{
							width:100% !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=mcnImageCardLeftImageContent],td[class=mcnImageCardRightImageContent]{
							padding-right:18px !important;
							padding-left:18px !important;
							padding-bottom:0 !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=mcnImageCardBottomImageContent]{
							padding-bottom:9px !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=mcnImageCardTopImageContent]{
							padding-top:18px !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=mcnImageCardLeftImageContent],td[class=mcnImageCardRightImageContent]{
							padding-right:18px !important;
							padding-left:18px !important;
							padding-bottom:0 !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=mcnImageCardBottomImageContent]{
							padding-bottom:9px !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=mcnImageCardTopImageContent]{
							padding-top:18px !important;
						}
					}
					@media only screen and (max-width: 480px){
						table[class=mcnCaptionLeftContentOuter] td[class=mcnTextContent],table[class=mcnCaptionRightContentOuter] td[class=mcnTextContent]{
							padding-top:9px !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=mcnCaptionBlockInner] table[class=mcnCaptionTopContent]:last-child td[class=mcnTextContent]{
							padding-top:18px !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=mcnBoxedTextContentColumn]{
							padding-left:18px !important;
							padding-right:18px !important;
						}
					}
					@media only screen and (max-width: 480px){
						table[id=templateContainer],table[id=templatePreheader],table[id=templateHeader],table[id=templateBody],table[id=templateFooter]{
							max-width:600px !important;
							width:100% !important;
						}
					}
					@media only screen and (max-width: 480px){
						h1{
							font-size:24px !important;
							line-height:125% !important;
						}
					}
					@media only screen and (max-width: 480px){
						h2{
							font-size:20px !important;
							line-height:125% !important;
						}
					}
					@media only screen and (max-width: 480px){
						h3{
							font-size:18px !important;
							line-height:125% !important;
						}
					}
					@media only screen and (max-width: 480px){
						h4{
							font-size:16px !important;
							line-height:125% !important;
						}
					}
					@media only screen and (max-width: 480px){
						table[class=mcnBoxedTextContentContainer] td[class=mcnTextContent]{
							font-size:18px !important;
							line-height:125% !important;
						}
					}
					@media only screen and (max-width: 480px){
						table[id=templatePreheader]{
							display:block !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=preheaderContainer] td[class=mcnTextContent]{
							font-size:14px !important;
							line-height:115% !important;
							padding-right:18px !important;
							padding-left:18px !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=headerContainer] td[class=mcnTextContent]{
							font-size:18px !important;
							line-height:125% !important;
							padding-right:18px !important;
							padding-left:18px !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=bodyContainer] td[class=mcnTextContent]{
							font-size:18px !important;
							line-height:125% !important;
							padding-right:18px !important;
							padding-left:18px !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=footerContainer] td[class=mcnTextContent]{
							font-size:14px !important;
							line-height:115% !important;
							padding-right:18px !important;
							padding-left:18px !important;
						}
					}
					@media only screen and (max-width: 480px){
						td[class=footerContainer] a[class=utilityLink]{
							display:block !important;
						}
					}
				</style>
			</head>
			<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0" style="margin: 0;padding: 0;background-color: #ffffff;">
				<center>
					<table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;margin: 0;padding: 0;background-color: #ffffff;height: 100% !important;width: 100% !important;">
						<tbody>
							<tr>
								<td align="center" valign="top" id="bodyCell" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;margin: 0;padding: 20px;border-top: 0;height: 100% !important;width: 100% !important;">
									<!-- BEGIN TEMPLATE // -->
									<table border="0" cellpadding="0" cellspacing="0" width="600" id="templateContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;border: 10px solid #00b1f5;">
										<tbody>
											<tr>
												<td align="center" valign="top" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
													<!-- BEGIN PREHEADER // -->
													<table border="0" cellpadding="0" cellspacing="0" width="600" id="templatePreheader" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;background-color: #00b1f5;border-top: 0;border-bottom: 0;">
														<tbody>
															<tr>
																<td valign="top" class="preheaderContainer" style="padding-top: 9px;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;"><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																	<table align="left" border="0" cellpadding="0" cellspacing="0" width="450" class="mcnTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																		<tbody class="mcnTextBlockOuter">
																			<tr>
																				<td valign="top" class="mcnTextBlockInner" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																					<table align="left" border="0" cellpadding="0" cellspacing="0" width="450" class="mcnTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																						<tbody>
																							<tr>
																								<td valign="top" class="mcnTextContent" style="padding-top: 9px;padding-left: 18px;padding-bottom: 9px;padding-right: 0;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;color: #ffffff;font-family: Helvetica;font-size: 11px;line-height: 125%;text-align: left;">
																									Confirmation of your $domain Account.
																								</td>
																							</tr>
																						</tbody>
																					</table>
																				</td>
																			</tr>
																		</tbody>
																	</table>
																</td>
															</tr>
														</tbody>
													</table>
													<!-- // END PREHEADER -->
												</td>
											</tr>
											<tr>
												<td align="center" valign="top" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
													<!-- BEGIN HEADER // -->
													<table border="0" cellpadding="0" cellspacing="0" width="600" id="templateHeader" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;background-color: #00b1f5;border-top: 0;border-bottom: 0;">
														<tbody>
															<tr>
																<td valign="top" class="headerContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;"><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnImageBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																	<table align="left" width="100%" border="0" cellpadding="0" cellspacing="0" class="mcnImageContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																		<tbody class="mcnImageBlockOuter">
																			<tr>
																				<td valign="top" style="padding: 9px;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;" class="mcnImageBlockInner">
																					<table align="left" width="100%" border="0" cellpadding="0" cellspacing="0" class="mcnImageContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																						<tbody>
																							<tr>
																								<td class="mcnImageContent" valign="top" style="padding-right: 9px;padding-left: 9px;padding-top: 0;padding-bottom: 0;text-align: center;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">    
																									<img align="center" alt="" src="https://gallery.mailchimp.com/0158f037752d173562593f2ba/images/header.2.png" width="564" style="max-width: 600px;padding-bottom: 0;display: inline !important;vertical-align: bottom;border: 0;line-height: 100%;outline: none;text-decoration: none;height: auto !important;" class="mcnImage">
																								</td>
																							</tr>
																						</tbody>
																					</table>
																				</td>
																			</tr>
																		</tbody>
																	</table>
																</td>
															</tr>
														</tbody>
													</table>
													<!-- // END HEADER -->
												</td>
											</tr>
											<tr>
												<td align="center" valign="top" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
													<!-- BEGIN BODY // -->
													<table border="0" cellpadding="0" cellspacing="0" width="600" id="templateBody" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;background-color: #ffffff;border-top: 0;border-bottom: 0;">
														<tbody>
															<tr>
																<td valign="top" class="bodyContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;"><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																	<table align="left" border="0" cellpadding="0" cellspacing="0" width="600" class="mcnTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																		<tbody class="mcnTextBlockOuter">
																			<tr>
																				<td valign="top" class="mcnTextBlockInner" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">    
																					<table align="left" border="0" cellpadding="0" cellspacing="0" width="600" class="mcnTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																						<tbody>
																							<tr>
																								<td valign="top" class="mcnTextContent" style="padding: 9px 18px;color: #FFFFFF;text-align: center;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;font-family: Helvetica;font-size: 15px;line-height: 150%;">
																									<br>
																									<p style="color: #606060;font-family: Helvetica;font-size: 15px;line-height: 150%;text-align: left;">
																										Thank you for signing up - this really makes our day!<br>
																										&nbsp;
																									</p>
																									<p style="color: #606060;font-family: Helvetica;font-size: 15px;line-height: 150%;text-align: left;">
																										Please access the following link (or copy it in your browser if for wathever reason you cannot click it) to use your new account:
																										<br>
																										<br>
																										<a href="$web" target="_blank">$web</a>
																										<br>
																										<br>
																										Username: $email
																										<br>
																										Password: <strong>$password</strong> (you can change it once logged in)
																										<br>
																										<br>
																										<br>
																										Thanks
																										<br>
																										<br>
																										The $brand Team
																									</p>
																								</td>
																							</tr>
																						</tbody>
																					</table>
																				</td>
																			</tr>
																		</tbody>
																	</table>
																</td>
															</tr>
														</tbody>
													</table>
													<!-- // END BODY -->
												</td>
											</tr>
											<tr>
												<td align="center" valign="top" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
													<!-- BEGIN FOOTER // -->
													<table border="0" cellpadding="0" cellspacing="0" width="600" id="templateFooter" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;background-color: #00b1f5;border-top: 0;border-bottom: 0;">
														<tbody>
															<tr>
																<td valign="top" class="footerContainer" style="padding-bottom: 9px;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;"><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																	<table align="left" border="0" cellpadding="0" cellspacing="0" width="600" class="mcnTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																		<tbody class="mcnTextBlockOuter">
																			<tr>
																				<td valign="top" class="mcnTextBlockInner" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">    
																					<table align="left" border="0" cellpadding="0" cellspacing="0" width="600" class="mcnTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;">
																						<tbody>
																							<tr>
																								<td valign="top" class="mcnTextContent" style="padding-top: 9px;padding-right: 18px;padding-bottom: 9px;padding-left: 18px;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;color: #ffffff;font-family: Helvetica;font-size: 11px;line-height: 125%;text-align: left;">
																									<em>Copyright © $year $company, All rights reserved.</em>
																									<br>
																									You are receiving this email because you have requested an account at $domain.
																									<br>
																									<br>
																									<!--<strong>Our mailing address is:</strong><br>		
																									<div class="vcard">
																										<span class="org fn">$company</span>
																										<div class="adr">
																											<div class="street-address">c/o 	Ashcroft Anthony, Heydon Lodge,</div>
																											<div class="extended-address">Flint Cross,  Newmarket Rd,</div>
																											<span class="locality">Heydon</span>, <span class="region">Hertfordshire</span>  <span class="postal-code">SG8 7PN</span> <div class="country-name">United Kingdom</div>
																											<br><a href="http://opheme.us7.list-manage1.com/vcard?u=0158f037752d173562593f2ba&amp;id=a0440c450c" class="hcard-download">Add us to your address book</a>
																										</div>
																										<br>
																									</div>-->
																								</td>
																							</tr>
																						</tbody>
																					</table>
																				</td>
																			</tr>
																		</tbody>
																	</table>
																</td>
															</tr>
														</tbody>
													</table>
													<!-- // END FOOTER -->
												</td>
											</tr>
										</tbody>
									</table>
									<!-- // END TEMPLATE -->
								</td>
							</tr>
						</tbody>
					</table>
				</center>
			</body>
		</html>
OPH;
		
		$plaintext = 'Thank you for signing up - this really makes our day!' . PHP_EOL . PHP_EOL;
		$plaintext .= 'Please access the following link (or copy it in your browser if for wathever reason you cannot click it) to use your new account: ' . $web . '.' . PHP_EOL . PHP_EOL;
		$plaintext .= 'Username: ' . $email . PHP_EOL;
		$plaintext .= 'Password: ' . $password . ' (you can change it once logged in)' . PHP_EOL . PHP_EOL;
		$plaintext .= 'Thanks' . PHP_EOL;
		$plaintext .= 'The ' . $brand . ' Team' . PHP_EOL;
		
		return $this->send_email($email, $subject, $body, $plaintext);
		
	}
	
	//send email
	private function send_email($to, $subject, $body, $plaintext) {
		
		$from_name = brand;
		$from_mail = 'noreply@' . domain;
		$alt_body = $plaintext;
	
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
	
	function change_details($details = null) {
		
		if (is_array($details)) {
			
			if (!isset($details['first-name'])) {
				$_SESSION['account_message'] = 'You must supply the First Name of the Business representative.';
				return false;
			}
			
			if (!isset($details['last-name'])) {
				$_SESSION['account_message'] = 'You must supply the Last Name of the Business representative.';
				return false;
			}
			
			if (!isset($details['phone'])) {
				$_SESSION['account_message'] = 'You must supply the Phone number of the Business representative.';
				return false;
			}
			
			if (!isset($details['business-type'])) {
				$_SESSION['account_message'] = 'You must supply the Business Name.';
				return false;
			}
			
			if (!isset($details['business-www']) || !stristr($details['business-www'], 'http://')) {
				$_SESSION['account_message'] = 'You must supply the Business Webpage. Example: http://www.yourbusiness.co.uk. The "http://" is mandatory.';
				return false;
			}
			
			if (!isset($details['old-password'])) {
				$_SESSION['account_message'] = 'You must supply the current Account Password in order to make any changes.';
				return false;
			}
			
			$query = "SELECT password, salt FROM users WHERE email = :email";
			$query_params = array(':email' => $_SESSION['user']['email']);
			
			try {
				$stmt = $this->db->prepare($query);
				$result = $stmt->execute($query_params);
			} catch(PDOException $ex) {
				$this->error_message($ex);
			}
			
			$row = $stmt->fetch();
			
			if($stmt->rowCount() == 1) {
			
				$check_password = hash('sha512', $details['old-password'] . $row['salt']);
						
				for($round = 0; $round < 65536; $round++) {
					$check_password = hash('sha512', $check_password . $row['salt']);
				}
				
				if($check_password === $row['password']) { //all OK, continue with making changes
			
					$query = 'UPDATE users SET firstname = :firstname, lastname = :lastname, phone = :phone, business_type = :btype, business_www = :bwww' . (strlen($details['new-password']) > 0?', password = :password':'') . ' WHERE email = :email';
					$query_params = array(
						':email' => $_SESSION['user']['email'],
						':firstname' => $details['first-name'],
						':lastname' => $details['last-name'],
						':phone' => $details['phone'],
						':btype' => $details['business-type'],
						':bwww' => $details['business-www']
					);
					
					if (strlen($details['new-password']) > 0) { //if new password is provided, include it in the changes
						$new_password = hash('sha512', $details['new-password'] . $row['salt']);
						for($round = 0; $round < 65536; $round++) {
							$new_password = hash('sha512', $new_password . $row['salt']);
						}
						$query_params = array_merge($query_params, array('password' => $new_password));
					}
					
					try {
						$stmt = $this->db->prepare($query);
						$result = $stmt->execute($query_params);
					} catch(PDOException $ex) {
						$this->error_message($ex);
					}
					
					if ($stmt->rowCount() == 1) { // If the changes were successful, update current session data
						
						//get data from Database, don't trust user input
						$query = "SELECT firstname, lastname, phone, business_type, business_www FROM users WHERE email = :email";
						$query_params = array(':email' => $_SESSION['user']['email']);
						
						try {
							$stmt = $this->db->prepare($query);
							$result = $stmt->execute($query_params);
						} catch(PDOException $ex) {
							$this->error_message($ex);
						}
						
						$row = $stmt->fetch();
						
						$_SESSION['user']['firstname'] = $row['firstname'];
						$_SESSION['user']['lastname'] = $row['lastname'];
						$_SESSION['user']['phone'] = $row['phone'];
						$_SESSION['user']['business_type'] = $row['business_type'];
						$_SESSION['user']['business_www'] = $row['business_www'];
						
						return true;
						
					}
					
					$_SESSION['account_message'] = 'Current password was correct, however all the other fields were empty.';
					return false;
					
				} else {
					
					$_SESSION['account_message'] = 'Current password is incorrect. Please try again.';
					return false;
				
				}
				
			} else {
				
				$_SESSION['account_message'] = 'Something went wrong, coulndn\'t find account info in our Database! Please report submit a report at http://support.opheme.com if this issue persists.';
				return false;
				
			}
			
		} else {
			
			$_SESSION['account_message'] = 'Something went wrong, form data could not be accessed! Please report submit a report at http://support.opheme.com if this issue persists.';
			return false;
		
		}
		
	}
	
	//http://roshanbh.com.np/2007/12/getting-real-ip-address-in-php.html
	function getUserIP() {

		if (!empty($_SERVER['HTTP_CLIENT_IP'])) { //check ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { //to check ip is pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		
		return $ip;
		
	}
	
}