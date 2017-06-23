<?php

	require_once("php/common.php");
	
	if (isset($_POST['screen_name']) && isset($_SESSION['web_id'])) {
		
		$opheme->campaign_setData($_POST);
		
		if (!$opheme->system_twitter_isBlacklisted()) {
			
			$opheme->system_twitter_addToBlacklist();
			
		} else {
			
			$opheme->system_twitter_removeFromBlacklist();
		}
		
		header('Location: /' . $_SESSION['web_id']);
		die ('Blacklist accessed. Redirecting...');
		
	} else {
		
		header('Location: http://www.opheme.com');
		die("Please use the link provided to you by oPheme to access this service.");
		
	}

?>