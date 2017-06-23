<?php
/**
 * @file
 * Take the user when they return from Twitter. Get access tokens.
 * Verify credentials and redirect to based on response from Twitter.
 */

	require_once("php/common.php");

	/* If the oauth_token is old redirect to the connect page. */
	if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
		
		$_SESSION['twitter_message'] = 'Current Twitter OAuth Token is old. Please try again.';
		
		header('Location: /account');
		die('Current Twitter OAuth Token is old. Please try again.');
	
	}
	
	/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
	
	/* Request access tokens from twitter */
	$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
	
	/* If HTTP response is 200 continue otherwise send to connect page to retry */
	if (200 == $connection->http_code) {
		
		/* Remove no longer needed request tokens */
		unset($_SESSION['oauth_token']);
		unset($_SESSION['oauth_token_secret']);
		
		//save to DB
		$ok = $opheme->system_twitter_saveUserAccessToken($access_token);
	
		if ($ok === true) {/* The user has been verified and the access tokens have been saved for future use */
			
			$_SESSION['twitter_ok'] = true;
			
			header('Location: /account');
			die('Success! Redirecting to Account...');
			
		} else {
			
			header('Location: /account');
			die('Database error.');
			
		}
		
	} else {
	  
		/* Save HTTP status for error dialog on connnect page.*/
		$_SESSION['twitter_message'] = 'Failed to obtain authorization from Twitter. Please try again.';
	  
		header('Location: /account');
		die('Connection failed. Please try again...');
	  
	}
