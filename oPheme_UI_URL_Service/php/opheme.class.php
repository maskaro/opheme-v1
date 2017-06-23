<?php

class opheme extends db {
	
	//plugin general settings
	protected $settings;
	
	//specific info
	protected $_c;
	
	//mongo
	protected $m;
	
	function __construct($_db_cred) {
		
		global $settings;
		
		$this->settings = $settings;
		
		$this->m = new MongoClient();
		
		parent::__construct($_db_cred);
		
	}
	
	function __destruct() {
		
		parent::__destruct();
		
	}
	
	function system_twitter_addToBlacklist() {
		
		$query = "SELECT password, salt FROM twitter_campaign_marketing_preferences WHERE screen_name = :screen_name";
		$query_params = array(':screen_name' => $this->_c['screen_name']);
		try { $stmt = $this->db->prepare($query); $result = $stmt->execute($query_params); } catch(PDOException $ex) { $this->error_message($ex); }
		
		if ($stmt->rowCount() == 1) {
			
			if (!isset($this->_c['password']) || strlen($this->_c['password']) < 8) {
				$_SESSION['twitter_blacklist_message'] = 'Blacklist preference not saved. Please fill in the Password field, at least 8 characters.';
				return false;
			}
			
			$password = $this->_c['password']; $row = $stmt->fetch(); $salt = $row['salt']; $db_pass = $row['password'];
			$password_hash = hash('sha512', $password . $salt);
			for($round = 0; $round < 65536; $round++) { $password_hash = hash('sha512', $password_hash . $salt); }
			if ($db_pass !== $password_hash) { $_SESSION['twitter_blacklist_message'] = 'Blacklist preference not saved. Incorrect password for current Twitter account.'; return false; }
				
		}
		
		$query = "INSERT INTO twitter_campaign_marketing_blacklist (screen_name) VALUES (:screen_name)";
		$query_params = array(
			':screen_name' => $this->_c['screen_name']
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if (!$result) {
			
			$_SESSION['twitter_blacklist_message'] = 'Database error. If this issue persists, please submit a report at http://support.opheme.com.';
			return false;
			
		}
		
		$_SESSION['twitter_blacklist_ok'] = 'Successfully added @' . $this->_c['screen_name'] . ' to blacklist.';
		return true;
		
	}
	
	function system_twitter_removeFromBlacklist() {
		
		if ($this->system_twitter_isBlacklisted()) {
			
			$query = "SELECT password, salt FROM twitter_campaign_marketing_preferences WHERE screen_name = :screen_name";
			$query_params = array(':screen_name' => $this->_c['screen_name']);
			try { $stmt = $this->db->prepare($query); $result = $stmt->execute($query_params); } catch(PDOException $ex) { $this->error_message($ex); }
			
			if ($stmt->rowCount() == 1) {
				
				if (!isset($this->_c['password']) || strlen($this->_c['password']) < 8) {
					$_SESSION['twitter_blacklist_message'] = 'Blacklist preference not saved. Please fill in the Password field, at least 8 characters.';
					return false;
				}
				
				$password = $this->_c['password']; $row = $stmt->fetch(); $salt = $row['salt']; $db_pass = $row['password'];
				$password_hash = hash('sha512', $password . $salt);
				for($round = 0; $round < 65536; $round++) { $password_hash = hash('sha512', $password_hash . $salt); }
				if ($db_pass !== $password_hash) { $_SESSION['twitter_blacklist_message'] = 'Blacklist preference not saved. Incorrect password for current Twitter account.'; return false; }
					
			}
			
		}
		
		$query = "DELETE FROM twitter_campaign_marketing_blacklist WHERE screen_name = :screen_name";
		$query_params = array(
			':screen_name' => $this->_c['screen_name']
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if (!$result) {
			
			$_SESSION['twitter_blacklist_message'] = 'Database error. If this issue persists, please submit a report at http://support.opheme.com.';
			return false;
			
		}
		
		$_SESSION['twitter_blacklist_ok'] = 'Successfully removed @' . $this->_c['screen_name'] . ' from blacklist.';
		return true;
		
	}
	
	function system_twitter_isBlacklisted() {
		
		$query = "SELECT 1 FROM twitter_campaign_marketing_blacklist WHERE screen_name = :screen_name";
		$query_params = array(
			':screen_name' => $this->_c['screen_name']
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if ($stmt->rowCount() == 1) {
			
			return true;
			
		}
		
		return false;
		
	}
	
	function savePreferences() {
		
		if (!isset($this->_c['preferences']) || count($this->_c['preferences']) == 0) {
			$_SESSION['twitter_message'] = 'Marketing Preferences not saved. Please select at least one Marketing Category.';
			return false;
		}
		
		if (!isset($this->_c['password']) || strlen($this->_c['password']) < 8) {
			$_SESSION['twitter_message'] = 'Marketing Preferences not saved. Please fill in the Password field, at least 8 characters.';
			return false;
		}
		
		$query = "SELECT 1 FROM twitter_campaign_marketing_preferences WHERE screen_name = :screen_name";
		$query_params = array(':screen_name' => $this->_c['screen_name']);
		try { $stmt = $this->db->prepare($query); $result = $stmt->execute($query_params); } catch(PDOException $ex) { $this->error_message($ex); }
		
		if ($stmt->rowCount() == 1) { //account preferences exist, check password and update preferences
			
			//check password
			$query = "SELECT password, salt FROM twitter_campaign_marketing_preferences WHERE screen_name = :screen_name";
			$query_params = array(':screen_name' => $this->_c['screen_name']);
			try { $stmt = $this->db->prepare($query); $result = $stmt->execute($query_params); } catch(PDOException $ex) { $this->error_message($ex); }
			$password = $this->_c['password']; $row = $stmt->fetch(); $salt = $row['salt']; $db_pass = $row['password'];
			$password_hash = hash('sha512', $password . $salt);
			for($round = 0; $round < 65536; $round++) { $password_hash = hash('sha512', $password_hash . $salt); }
			if ($db_pass !== $password_hash) { $_SESSION['twitter_message'] = 'Marketing Preferences not saved. Incorrect password for current Twitter account.'; return false; }
			
			//update preferences
			$query = "UPDATE twitter_campaign_marketing_preferences SET preferences = :preferences WHERE screen_name = :screen_name";
			$query_params = array(':preferences' => implode(',', $this->_c['preferences']), ':screen_name' => $this->_c['screen_name']);
			try { $stmt = $this->db->prepare($query); $result = $stmt->execute($query_params); } catch(PDOException $ex) { $this->error_message($ex); }
			if (!$result) { $_SESSION['twitter_message'] = 'Database error. If this issue persists, please submit a report at http://support.opheme.com.'; return false; }
			
		} else {
			
			if (strlen($this->_c['confirm-password']) > 0) { //add password to db
				
				if ($this->_c['password'] === $this->_c['confirm-password']) {
					
					$password = $this->_c['password']; $salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647)); $password_hash = hash('sha512', $password . $salt);
					for($round = 0; $round < 65536; $round++) { $password_hash = hash('sha512', $password_hash . $salt); }
					
					$query = "INSERT INTO twitter_campaign_marketing_preferences (screen_name, preferences, password, salt) VALUES (:screen_name, :preferences, :password, :salt)";
					$query_params = array( ':preferences' => implode(',', $this->_c['preferences']), ':screen_name' => $this->_c['screen_name'], ':password' => $password_hash, ':salt' => $salt);
					try { $stmt = $this->db->prepare($query); $result = $stmt->execute($query_params); } catch(PDOException $ex) { $this->error_message($ex); }
					if (!$result) { $_SESSION['twitter_message'] = 'Database error. If this issue persists, please submit a report at http://support.opheme.com.'; return false; }
				
				} else {
					$_SESSION['twitter_message'] = 'Marketing Preferences not saved. Passwords do not match. Please make sure you enter the same password in both Password fields.';
					return false;
				}
				
			} else {
				$_SESSION['twitter_message'] = 'Marketing Preferences not saved. Please make sure you fill in both Password fields.';
				return false;
			}
			
		}
		
		$_SESSION['twitter_ok'] = 'Successfully saved Marketing Preferences for @' . $this->_c['screen_name'] . '.';
		return true;
		
	}
	
	function campaign_setData($data) {
		
		$this->_c = $data;
		
	}
	
	function sanitize_val($val) {
		
		$return = str_replace(array('$', ',', 'function'), '', strval($val));
		
		return (string)$return;
		
	}
	
	//return full camp specs
	function campaign_getSpecs() {
		
		//select a database
		$db = $this->m->jobs;
		// select a collection (analogous to a relational database's table)
		$collection = $db->campaigns;
		
		$query = array('web_id' => $this->sanitize_val($this->_c['web_id']));
		$doc = $collection->findOne($query);
		
		if (isset($doc['campaign_id'])) {
			
			//what we return for further use
			$return = array();
			
			//get the web id as well
			$return['web_id'] = $doc['web_id'];
			
			//select a database
			$db = $this->m->messages;
			// select a collection (analogous to a relational database's table)
			$collection = $db->tweets;
			
			//get the original tweet info
			$query = array('id_str' => $doc['tweet_id']);
			$tweet = $collection->findOne($query);
			$return['tweet'] = $tweet;
			
			//get campaign info
			$query = "SELECT * FROM campaigns WHERE id = :id";
			$query_params = array(':id' => $doc['campaign_id']);
			
			try {
				$stmt = $this->db->prepare($query);
				$result = $stmt->execute($query_params);
			} catch(PDOException $ex) {
				$this->error_message($ex);
			}
			
			$campaign = $stmt->fetch();
			$return['campaign'] = $campaign;
			
			//campaign business info
			$query = "SELECT business_type, business_www FROM secure_login.users WHERE email = :email";
			$query_params = array(':email' => $campaign['user_id']);
			
			try {
				$stmt = $this->db->prepare($query);
				$result = $stmt->execute($query_params);
			} catch(PDOException $ex) {
				$this->error_message($ex);
			}
			
			$business = $stmt->fetch();
			$return['business']['name'] = $business['business_type'];
			$return['business']['www'] = $business['business_www'];
			
			//user preferences
			$query = "SELECT preferences as prefs FROM twitter_campaign_marketing_preferences WHERE screen_name = :screen_name";
			$query_params = array(':screen_name' => $tweet['user']['screen_name']);
			
			try {
				$stmt = $this->db->prepare($query);
				$result = $stmt->execute($query_params);
			} catch(PDOException $ex) {
				$this->error_message($ex);
			}
			
			if ($stmt->rowCount() == 1) {
			
				$prefs = $stmt->fetch();
				$return['user_prefs'] = explode(',', $prefs['prefs']);
			
			} else $return['user_prefs'] = array();
			
			$return['blacklisted'] = $this->system_twitter_isBlacklisted();
			
			return $return;
			
		}
		
		return false;
		
	}
	
}