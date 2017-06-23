#!/usr/bin/php -q
<?php

	//http://api.twitter.com/1.1/search/tweets.json?q=%20&count=100&geocode=lat,lng,rad&since_id=last_id
	//limits: 	180 requests / 15min / user
	//			450 requests / 15min / app
	
	$debug = true; //true - trigger PHP notices with OAuth messages
	
	$campaign_string = $_SERVER['argv'][1];
	$php_folder = realpath($_SERVER['argv'][2]);
	$module_dir = realpath($_SERVER['argv'][3] . '/CAMPAIGN');
	
	require_once($php_folder . '/settings.php');
	require_once($php_folder . '/db.class.php');
	require_once($php_folder . '/opheme.class.php');
	require_once($php_folder . '/twitteroauth/twitteroauth.php');
	
	$campaign_tmp = explode('!', $campaign_string);
	$data = array(
		'campaign_id' => $campaign_tmp[0],
		'module' => $campaign_tmp[1],
		'sub_id' => $campaign_tmp[2]
	);
	
	$opheme = new opheme($db_gen);
	$opheme->system_setData($data);
	
	$campaign = $opheme->campaign_getSpecs();
	
	//suspended campaign
	if (intval($campaign['suspended']) == 1) exit;
	
	//job creation date
	$job_added = $campaign['added'];
	
	//get some user info
	$allowance = $opheme->system_getUserAllowance();
	$account_suspended = $opheme->system_getUserAccountSuspended($campaign['user_id']);
	$account_created = $opheme->system_getUserAccountCreationDate($campaign['user_id']);
	$business = $opheme->system_getUserBusiness($campaign['user_id']);
	
	//campaign info
	$message_count = $campaign['message_count'];
	
	/* USER LIMIT CHECKS */
	include($php_folder . '/checks.php');
	
	//script run check
	
	//check DATE
	$continue = false;
	$start = strtotime($campaign['start_date']);
	$end = strtotime($campaign['end_date']);
	$today = strtotime(date('Y-m-d'));
	if ($today >= $start && $today <= $end) $continue = true;
	if ($continue == false) exit;
	
	//check WEEKDAY
	$continue = false;
	$weekdays = explode(',', $campaign['weekdays']);
	$today = getDayOfWeek();
	foreach ($weekdays as $day) { if ($day == $today) { $continue = true; break; } }
	if ($continue == false) exit;
	
	//check TIMEs
	$continue = false;
	$reference_time = date('H:i');
	$absolute = strtotime($reference_time);
	if (
		strtotime($campaign['start_time'], $absolute) <= $absolute
		&&
		strtotime($campaign['end_time'], $absolute) >= $absolute
	) $continue = true;
	if ($continue == false) exit;
	
	//get user token and intiate twitter connection
	$token = $opheme->system_twitter_getUserToken($campaign['user_id']);
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
			'q' => 				(strlen($campaign['filter'])>0?rawurlencode($campaign['filter']):rawurlencode(' ')),
			'count' => 			'100',
			'geocode' =>		$campaign['centre_lat'] . ',' . $campaign['centre_lng'] . ',' . $campaign['radius'] . 'mi',
			'since_id' =>		$campaign['since_id'],
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
			
			$filter_ex = explode(' ', $campaign['filter_ex']); //exclusion filter
		
			/*** Parse and Store results ***/
			foreach ($content_arr_search['statuses'] as $tweet) {
				
				//skip blacklisted users
				if ($opheme->system_twitter_isBlacklisted($tweet['user']['screen_name'])) continue;
				
				//skip if campaign type not in user preferences
				if (!$opheme->system_twitter_isPreferenced($tweet['user']['screen_name'], $campaign['category'])) continue;
				
				//check tweet timestamp - if older than a day, skip it
				$until = strtotime('+1 hour', strtotime($tweet['created_at'])); $now = strtotime('now');
				if ($now >= $until) continue;
				
				//if tweet is valid
				//TODO: maybe in the future we won't care for coordinates?
				if (isset($tweet['geo']['coordinates'])) {
					
					//if message limit has been reached, skip tweet
					if ($allowance['messages_limit'] > 0) {
						if ($campaign['message_count'] >= $allowance['messages_limit']) continue;
					}
					
					//exclusion filter
					$stop = false;
					if (strlen($filter_ex[0]) > 0) { foreach ($filter_ex as $keyword) { if (is_numeric(stripos($tweet['text'], $keyword))) { $stop = true; break; } } }
					if ($stop == true) continue;
					
					//if tweet isn't already in the DB, save it for future reference
					if (!$opheme->system_twitter_tweetExists($tweet['id_str'])) $opheme->system_twitter_tweetStoreCampaign($tweet, $campaign['id']);
					
					//if user has not already been messaged
					if (!$opheme->system_twitter_tweetToUserExists($tweet['user']['id_str'], $campaign['id'])) {
						
						$web_id = uniqid('', true) . '-' .  uniqid('', true) . '-' .  uniqid('', true); //unique web id
						$business = $opheme->system_getUserBusiness($campaign['user_id']);
						$url = 'http://ophe.me/' . $web_id;
						$parsed_text = str_replace(array('%r', '%c'), array('@' . $tweet['user']['screen_name'], $business), $campaign['response_text']);
						$text = $parsed_text . ' ' . $url; //text to send to people
						
						$params = array(
							'in_reply_to_status_id' => 	$tweet['id_str'],
							'status' => 	$text
						);
						$content = $connection->post('statuses/update', $params);
						
						$content_arr_direct = objectToArray($content);
						
						//debugging - display last API call
						if ($debug) {
							$message = date("Y-m-d H:i:s") . ' - ' . $connection->http_code . ': ' . $connection->url;
							if (isset($content_arr_direct['errors'])) {
								foreach($content_arr_direct['errors'] as $error)
									$message .= ' / ' . implode(', ', $error);
								$message .= ' / ' . implode(', ', $params);
							}
							trigger_error($message);
						}
						
						//store it to disk
						if ($connection->http_code == 200) {
							
							$content_arr_direct['coords'] = $tweet['geo']['coordinates'];
							$opheme->system_twitter_tweetSentStore($content_arr_direct, $campaign['id'], $tweet['id_str'], $web_id);
							
						}
					
					}
					
				}
				
			}
			
		}
		
		//store max_id
		if (isset($content_arr_search['search_metadata'])) $opheme->system_twitter_jobSetMaxId($content_arr_search['search_metadata']['max_id_str'], $campaign['id'], 'campaigns');
		
	} else if (date("Y-m-d H:i:s") . ' - ' . $connection->http_code == 401 /* unauthorized */ || date("Y-m-d H:i:s") . ' - ' . $connection->http_code == 89 /* invalid/expired token */) { //remove stored credentials
			$opheme->system_twitter_cancelUserToken($campaign['user_id']);
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
