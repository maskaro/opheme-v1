<?php

	require_once("php/common.php");
	
	if (isset($_POST['screen_name']) && isset($_SESSION['web_id'])) {
		
		$opheme->campaign_setData($_POST);
		
		$opheme->savePreferences();
		
		header('Location: /' . $_SESSION['web_id']);
		die ('Marketing preferences accessed. Redirecting...');
		
	} else {
		
		header('Location: http://www.opheme.com');
		die("Please use the link provided to you by oPheme to access this service.");
		
	}

?>