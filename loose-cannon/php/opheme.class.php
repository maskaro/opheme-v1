<?php

class opheme extends db {
	
	//plugin general settings
	protected $settings;
	
	//specific info
	protected $_d;
	
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
	
	function system_twitter_isBlacklisted($screen_name) {
		
		$query = "SELECT 1 FROM twitter_blacklist WHERE screen_name = :screen_name";
		$query_params = array(
			':screen_name' => $screen_name
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
	
	function system_twitter_isPreferenced($screen_name, $pref) {
		
		$query = "SELECT preferences as prefs FROM twitter_preferences WHERE screen_name = :screen_name";
		$query_params = array(
			':screen_name' => $screen_name
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if ($stmt->rowCount() == 1) {
			
			$row = $stmt->fetch();
			
			if (stristr($row['prefs'], $pref)) return true;
			
			return false;
			
		}
		
		return true;
		
	}
	
	//gets user allowance based on sub_id
	function system_getUserAllowance() {
		
		$query = "SELECT * FROM sub_limits WHERE id = :user_sub";
		$query_params = array(':user_sub' => $this->_d['sub_id']);
		
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
	
	//returns user business type based on email
	function system_getUserBusiness($email) {
		
		$query = "SELECT business_type FROM secure_login.users WHERE email = :email";
		$query_params = array(':email' => $email);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() == 1) {
		
			$row = $stmt->fetch();
			
			return $row['business_type'];
			
		}
		
		return false;
		
	}
	
	//returns user account suspension status
	function system_getUserAccountSuspended($email) {
		
		$query = "SELECT suspended FROM secure_login.users WHERE email = :email";
		$query_params = array(':email' => $email);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() == 1) {
		
			$row = $stmt->fetch();
			
			return $row['suspended'];
			
		}
		
		return false;
		
	}
	
	//returns user account creation date
	function system_getUserAccountCreationDate($email) {
		
		$query = "SELECT created FROM secure_login.users WHERE email = :email";
		$query_params = array(':email' => $email);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() == 1) {
		
			$row = $stmt->fetch();
			
			return $row['created'];
			
		}
		
		return false;
		
	}
	
	//returns user subscription level based on email
	function system_getUserSubscription($email) {
		
		$query = "SELECT subscription FROM secure_login.users WHERE email = :email";
		$query_params = array(':email' => $email);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() == 1) {
		
			$row = $stmt->fetch();
			
			return $row['subscription'];
			
		}
		
		return false;
		
	}
	
	//gets user access tokens from DB
	function system_twitter_getUserToken($email) {
		
		$query = "SELECT * FROM twitter_keys WHERE user_id = :email";
		$query_params = array(':email' => $email);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() == 1) {
			
			$row = $stmt->fetch();
			
			$token = array(
				'token' => $row['token'],
				'token_secret' => $row['token_secret']
			);
			
			return $token;
			
		}
		
		return false;
		
	}
	
	//gets user access tokens from DB
	function system_twitter_cancelUserToken($email) {
		
		$query = "DELETE FROM twitter_keys WHERE user_id = :email";
		$query_params = array(':email' => $email);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() == 1) {
			
			return true;
			
		}
		
		return false;
		
	}
	
	//must store tweet to mongodb and associate id with job
	function system_twitter_tweetStoreCampaign($tweet, $campaign_id) {
		
		//make sure timezone is correct
		$tweet['created_at'] = date("D M j G:i:s T Y", strtotime($tweet['created_at']));
		
		//Save tweet
		
		//select a database
		$db = $this->m->messages;
		//select a collection (analogous to a relational database's table)
		$collection = $db->tweets;
		//add a record
		$collection->insert($tweet);
		
		//Associate tweet with job
		
		//select a database
		$db = $this->m->jobs;
		// select a collection (analogous to a relational database's table)
		$collection = $db->campaigns;
		//add a record
		$collection->insert(array('campaign_id' => $campaign_id, 'tweet_id' => $tweet['id_str']));
		
	}
	
	//must store tweet to mongodb and associate id with job
	function system_twitter_tweetStoreDiscover($tweet, $discover_id) {
		
		//make sure timezone is correct
		$tweet['created_at'] = date("D M j G:i:s T Y", strtotime($tweet['created_at']));
		
		//Save tweet
		
		//select a database
		$db = $this->m->messages;
		//select a collection (analogous to a relational database's table)
		$collection = $db->tweets;
		//add a record
		$collection->insert($tweet);
		
		//Associate tweet with job
		
		//select a database
		$db = $this->m->jobs;
		// select a collection (analogous to a relational database's table)
		$collection = $db->discovers;
		//add a record
		$collection->insert(array('discover_id' => $discover_id, 'tweet_id' => $tweet['id_str']));
		
		//Increment Discover sent tracker
		
		$query = "SELECT message_count FROM discovers WHERE id = :id";
		$query_params = array(':id' => $discover_id);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if ($stmt->rowCount() != 1) return false;
		
		$row = $stmt->fetch();
		$message_count = $row['message_count'] + 1;
		
		$query = "UPDATE discovers SET message_count = :message_count WHERE id = :id";
		$query_params = array(
			':message_count' => $message_count,
			':id' => $discover_id
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
	}
	
	//must store tweet to mongodb and associate id with campaign job
	function system_twitter_tweetSentStore($sent_tweet, $campaign_id, $initial_tweet_id, $web_id) {
		
		//make sure timezone is correct
		$sent_tweet['created_at'] = date("D M j G:i:s T Y", strtotime($sent_tweet['created_at']));
		
		//Save tweet
		
		//select a database
		$db = $this->m->messages;
		//select a collection (analogous to a relational database's table)
		$collection = $db->tweets_sent;
		//add a record
		$collection->insert($sent_tweet);
		
		//Associate tweet with campaign job
		
		//select a database
		$db = $this->m->jobs;
		// select a collection (analogous to a relational database's table)
		$collection = $db->campaigns_sent;
		//add a record
		$collection->insert(array('campaign_id' => $campaign_id, 'sent_tweet_id' => $sent_tweet['id_str'], 'tweet_id' => $initial_tweet_id, 'recipient_id' => $sent_tweet['in_reply_to_user_id_str'], 'web_id' => $web_id));
		
		//Increment Campaign sent tracker
		
		$query = "SELECT message_count FROM campaigns WHERE id = :id";
		$query_params = array(':id' => $campaign_id);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if ($stmt->rowCount() != 1) return false;
		
		$row = $stmt->fetch();
		$message_count = $row['message_count'] + 1;
		
		$query = "UPDATE campaigns SET message_count = :message_count WHERE id = :id";
		$query_params = array(
			':message_count' => $message_count,
			':id' => $campaign_id
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		return true;
		
	}
	
	//checks if tweet already exists in DB
	function system_twitter_tweetExists($id) {
		
		//select a database
		$db = $this->m->messages;
		//select a collection (analogous to a relational database's table)
		$collection = $db->tweets;
		
		$query = array("id_str" => $id);
		$cursor = $collection->findOne($query);
		
		if (isset($cursor["id_str"])) return true;
		
		return false;
		
	}
	
	//checks if tweet recipient already exists in DB for current job
	function system_twitter_tweetToUserExists($recipient_id, $campaign_id) {
		
		//select a database
		$db = $this->m->jobs;
		//select a collection (analogous to a relational database's table)
		$collection = $db->campaigns_sent;
		
		$query = array("recipient_id" => $recipient_id, "campaign_id" => strval($campaign_id));
		$cursor = $collection->findOne($query);
		
		if (isset($cursor["recipient_id"])) return true;
		
		return false;
		
	}
	
	//sets max id for current job
	function system_twitter_jobSetMaxId($max_id, $id, $type) {
		
		$query = "UPDATE $type SET since_id = :since_id WHERE id = :id";
		$query_params = array(
			':since_id' => $max_id,
			':id' => $id
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
	
	//sets data array for current object
	function system_setData($data) {
		
		$this->_d = $data;
		
	}
	
	//return full camp specs
	function campaign_getSpecs() {
		
		$query = "SELECT * FROM campaigns WHERE id = :id";
		$query_params = array(':id' => $this->_d['campaign_id']);
		
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
	
	//return full disc specs
	function discover_getSpecs() {
		
		$query = "SELECT * FROM discovers WHERE id = :id";
		$query_params = array(':id' => $this->_d['discover_id']);
		
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
	
	//must return
	//JOB_ID!MODULE!SUB_ID
	//all on separate rows
	function system_discover_getJobs() {
		
		$query = "SELECT id, user_id FROM discovers WHERE suspended = 0";
		$query_params = array();
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() > 0) {
			
			$rows = $stmt->fetchAll();
			$return = '';
			
			foreach ($rows as $row) {
				
				$sub_id = $this->system_getUserSubscription($row['user_id']);
				$return .= $row['id'] . '!DISCOVER!' . $sub_id . PHP_EOL;
				
			}
			
			return $return;
			
		}
		
		return '';
		
	}
	
	//must return
	//JOB_ID!MODULE!SUB_ID
	//all on separate rows
	function system_campaign_getJobs() {
		
		$query = "SELECT id, user_id FROM campaigns WHERE suspended = 0";
		$query_params = array();
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() > 0) {
		
			$rows = $stmt->fetchAll();
			$return = '';
			
			foreach ($rows as $row) {
				
				$sub_id = $this->system_getUserSubscription($row['user_id']);
				$return .= $row['id'] . '!CAMPAIGN!' . $sub_id . PHP_EOL;
				
			}
			
			return $return;
			
		}
		
		return '';
		
	}
	
	//truncates a decimal number to the number of places specified
	function trunc($number, $places) { $power = pow(10, $places); return floor($number * $power) / $power; }
	
}