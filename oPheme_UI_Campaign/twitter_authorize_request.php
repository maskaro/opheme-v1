<?php

	require_once("php/common.php");
	
	/* Build TwitterOAuth object with client credentials. */
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
	 
	/* Get temporary credentials. */
	$request_token = $connection->getRequestToken(OAUTH_CALLBACK);
	
	/* Save temporary credentials to session. */
	$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
	$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
	 
	/* If last connection failed don't display authorization link. */
	switch ($connection->http_code) {
		case 200:
			/* Build authorize URL and redirect user to Twitter. */
			$url = $connection->getAuthorizeURL($token);
			header('Location: ' . $url);
			break;
		default:
			/* Save HTTP status for error dialog on connnect page.*/
			$_SESSION['twitter_message'] = 'Failed to obtain authorization from Twitter. Please try again.';
		  
			header('Location: /account');
			die('Connection failed. Please try again...');
	}
