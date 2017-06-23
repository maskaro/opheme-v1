<?php

	require_once("common.php");
	
	//AJAX request
	if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
		
		$opheme->campaign_setData($_REQUEST);
		//message that will be returned to javascript
		$message = null;
		
		//job
		switch ($_REQUEST['do']) {
			case 'campaign_getNewMessages':
				$message = $opheme->campaign_getNewMessages();
				break;
			case 'campaign_getSpecs':
				$message = $opheme->campaign_getSpecs();
				break;
			case 'campaign_pause':
				$message = $opheme->campaign_pause();
				break;
			case 'campaign_unPause':
				$message = $opheme->campaign_unPause();
				break;
			case 'campaign_twitterFollow':
				$message = $opheme->campaign_twitterFollow();
				break;
			default:
				$message = 'Unrecognised function.';
				break;
		}
		
		//return message after encoding
		echo json_encode($message);
		
		//stop this PHP script
		exit;
	
	}
