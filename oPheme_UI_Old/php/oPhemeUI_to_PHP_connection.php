<?php

include('../config/settings.php');
include('oPhemeUI.php');

//AJAX request
if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {

	if (count($_POST)) foreach (@$_POST as $key => $value) $$key = $value;
	
	//put together all the info the class needs to function
	$info = array(
		'job_info' => array (
			'id' => @$id,
			'data' => @$form,
			'info' => @$job_info
		),
		'settings' => $settings
	);
	
	//initiate class instance
	
	//checks data directly from form
	if (isset($info['job_info']['data']['source'])) $module = $info['job_info']['data']['source'];
	//checks data from AJAX call
	elseif (isset($info['job_info']['info']['source'])) $module = $info['job_info']['info']['source'];
	//initial AJAX call for getting job specs
	else $module = null;
	
	if ($module) {
		
		//filter module variable a bit
		$module = ucfirst(strtolower(htmlspecialchars($module)));
		
		//what module is required?
		switch ($module) {
			case 'Twittergeo':
				$module = 'Twitter';
				break;
			default:
				$module = 'Twitter';
				break;
		}
		
		eval("\$class = new oPhemeUI_$module(\$info);");
	
	} else $class = new oPhemeUI_Twitter($info); //defaults to twitter module for getting job specs
	
	//message that will be returned to javascript
	$message = null;
	
	//job
	switch ($do) {
		case 'createJob':
			$message = $class->createJob();
			break;
		case 'getJobSpecs':
			$message = $class->getJobSpecs();
			break;
		case 'checkJob':
			$message = $class->checkJob();
			break;
		case 'getJobListings':
			$message = $class->getJobListings();
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
	
?>
