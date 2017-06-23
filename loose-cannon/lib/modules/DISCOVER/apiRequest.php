#!/usr/bin/php -q
<?php

	//http://api.twitter.com/1.1/search/tweets.json?q=%20&count=100&geocode=lat,lng,rad&since_id=last_id
	//limits: 	180 requests / 15min / user
	//			450 requests / 15min / app
	
	$debug = true; //true - trigger PHP notices with OAuth messages
	
	$discover_string = $_SERVER['argv'][1];
	$php_folder = realpath($_SERVER['argv'][2]);
	$module_dir = realpath($_SERVER['argv'][3] . '/DISCOVER');
	
	require_once($php_folder . '/settings.php');
	require_once($php_folder . '/db.class.php');
	require_once($php_folder . '/opheme.class.php');
	require_once($php_folder . '/twitteroauth/twitteroauth.php');
	
	$discover_tmp = explode('!', $discover_string);
	$data = array(
		'discover_id' => $discover_tmp[0],
		'module' => $discover_tmp[1],
		'sub_id' => $discover_tmp[2]
	);
	
	$opheme = new opheme($db_gen);
	$opheme->system_setData($data);
	
	$discover = $opheme->discover_getSpecs();
	
	//suspended campaign
	if (intval($discover['suspended']) == 1) exit;
	
	//job creation date
	$job_added = $discover['added'];
	
	//get some user info
	$allowance = $opheme->system_getUserAllowance();
	$account_suspended = $opheme->system_getUserAccountSuspended($discover['user_id']);
	$account_created = $opheme->system_getUserAccountCreationDate($discover['user_id']);
	$business = $opheme->system_getUserBusiness($discover['user_id']);
	
	//campaign info
	$message_count = $discover['message_count'];
	
	/* USER LIMIT CHECKS */
	include($php_folder . '/checks.php');
	
	//script run check
	
	if ($discover['start_date'] != '0000-00-00' && $discover['end_date'] != '0000-00-00') { //check DATE
		$continue = false;
		$start = strtotime($discover['start_date']);
		$end = strtotime($discover['end_date']);
		$today = strtotime(date('Y-m-d'));
		if ($today >= $start && $today <= $end) $continue = true;
		if ($continue == false) exit;
	}
	
	if (strlen($discover['weekdays']) > 0) { //check WEEKDAY
		$continue = false;
		$weekdays = explode(',', $discover['weekdays']);
		$today = getDayOfWeek();
		foreach ($weekdays as $day) { if ($day == $today) { $continue = true; break; } }
		if ($continue == false) exit;
	}
	
	if ($discover['start_time'] != '00:00:00' || $discover['end_time'] != '00:00:00') { //check TIMEs
		$continue = false;
		$reference_time = date('H:i');
		$absolute = strtotime($reference_time);
		if (
			strtotime($discover['start_time'], $absolute) <= $absolute
			&&
			strtotime($discover['end_time'], $absolute) >= $absolute
		) $continue = true;
		if ($continue == false) exit;
	}
	
	//get user token and intiate twitter connection
	$token = $opheme->system_twitter_getUserToken($discover['user_id']);
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $token['token'], $token['token_secret']);
	
	/* only 15 calls per 15min window */
	$content = $connection->get('account/verify_credentials');
	$content_arr_verify = objectToArray($content);
	
	//debugging - display last API call
	if ($debug) {
		$message = date("Y-m-d H:i:s") . ' - ' . $connection->http_code . ': ' . $connection->url;
		if (isset($content_arr_verify['errors'])) {
			foreach($content_arr_verify['errors'] as $error)
				$message .= ' / ' . implode(', ', $error);
		}
		trigger_error($message);
	}
	
	if ($connection->http_code == 200 || $connection->http_code == 429 /* rate limit exceeded (15min window) */) { //authenticated, might have to wait 15min before checking credentials again due to limitation
		
		$params = array(
			'q' => 				(strlen($discover['filter'])>0?rawurlencode($discover['filter']):rawurlencode(' ')),
			'count' => 			'100',
			'geocode' =>		$discover['centre_lat'] . ',' . $discover['centre_lng'] . ',' . $discover['radius'] . 'mi',
			'since_id' =>		$discover['since_id'],
			'result_type' => 	'recent'
		);
		//get content - multi-layered array
		$content = $connection->get('search/tweets', $params);
		$content_arr_search = objectToArray($content);
		
		//debugging - display last API call
		if ($debug) {
			$message = date("Y-m-d H:i:s") . ' - ' . $connection->http_code . ': ' . $connection->url;
			if (isset($content_arr_search['errors'])) {
				foreach($content_arr_search['errors'] as $error)
					$message .= ' / ' . implode(', ', $error);
				$message .= ' / ' . implode(', ', $params);
			}
			trigger_error($message);
		}
		
		//if all went OK
		if ($connection->http_code == 200) {
			
			$filter_ex = explode(' ', $discover['filter_ex']); //exclusion filter
		
			/*** Parse and Store results ***/
			foreach ($content_arr_search['statuses'] as $tweet) {
				
				//check tweet timestamp - if older than a day, skip it
				/*$until = strtotime('+1 day', strtotime($tweet['created_at'])); $now = strtotime('now');
				if ($now >= $until) continue;*/
				
				//if tweet is valid
				//TODO: maybe in the future we won't care for coordinates?
				if (isset($tweet['geo']['coordinates'])) {
					
					//if message limit has been reached, just continue
					if ($allowance['messages_limit'] > 0) {
						if ($discover['message_count'] >= $allowance['messages_limit']) continue;
					}
					
					//exclusion filter
					$stop = false;
					if (strlen($filter_ex[0]) > 0) { foreach ($filter_ex as $keyword) { if (is_numeric(stripos($tweet['text'], $keyword))) { $stop = true; break; } } }
					if ($stop == true) continue;
					
					//if tweet isn't already in the DB, save it for future reference
					if (!$opheme->system_twitter_tweetExists($tweet['id_str'])) $opheme->system_twitter_tweetStoreDiscover($tweet, $discover['id']);
					
				}
				
			}
			
		}
		
		//store max_id
		if (isset($content_arr_search['search_metadata'])) $opheme->system_twitter_jobSetMaxId($content_arr_search['search_metadata']['max_id_str'], $discover['id'], 'discovers');
		
	} else if ($connection->http_code == 401 /* unauthorized */ || $connection->http_code == 89 /* invalid/expired token */) { //remove stored credentials
			$opheme->system_twitter_cancelUserToken($discover['user_id']);
	}
	
	//http://www.if-not-true-then-false.com/2009/php-tip-convert-stdclass-object-to-multidimensional-array-and-convert-multidimensional-array-to-stdclass-object/
	function objectToArray($d) {
		if (is_object($d)) {
			// Gets the properties of the given object
			// with get_object_vars function
			$d = get_object_vars($d);
		}
		if (is_array($d)) {
			/*
			* Return array converted to object
			* Using __FUNCTION__ (Magic constant)
			* for recursive call
			*/
			return array_map(__FUNCTION__, $d);
		}
		else {
			// Return array
			return $d;
		}
	}
	
	function getDayOfWeek() {
		
		$pTimezone = 'Europe/London';
		
		$userDateTimeZone = new DateTimeZone($pTimezone);
		$UserDateTime = new DateTime("now", $userDateTimeZone);
	
		$offsetSeconds = $UserDateTime->getOffset(); 
	
		return gmdate("l", time() + $offsetSeconds);
	
	}

?>
