<?php

	require_once("common.php");
	
	//AJAX request
	if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
		
		$opheme->discover_setData($_REQUEST);
		//message that will be returned to javascript
		$message = null;
		
		//job
		switch ($_REQUEST['do']) {
			case 'discover_getNewMessages':
				$message = $opheme->discover_getNewMessages();
				break;
			case 'discover_getSpecs':
				$message = $opheme->discover_getSpecs();
				break;
			case 'discover_pause':
				$message = $opheme->discover_pause();
				break;
			case 'discover_unPause':
				$message = $opheme->discover_unPause();
				break;
			case 'discover_twitterFollow':
				$message = $opheme->discover_twitterFollow();
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
