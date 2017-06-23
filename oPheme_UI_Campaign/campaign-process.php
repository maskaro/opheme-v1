<?php

	require_once("php/common.php");
	require_once("php/protect.php");
	
	if (!empty($_POST)) {
		
		$data = $_POST;
		if (!empty($_FILES)) $data['uploaded_banner_file'] = $_FILES['campaign_banner'];

		$opheme->campaign_setData($data);
		
		if (@$_POST['campaign_id'] == 0) { //create campaign
			
			$ok = $opheme->campaign_create();
			
		} elseif (@is_numeric($_POST['campaign_id'])) {
			
			if (@$_POST['campaign_delete'] == 1) {
				
				//delete
				$ok = $opheme->campaign_delete();
				
			} else {
				
				//edit
				$ok = $opheme->campaign_edit();
				
			}
			
		}
		
		if ($ok === true) {
			
			header('Location: /campaigns');
			die ('Campaign has been successfully created. Redirecting to Campaigns...');
			
		} else {
			
			header('Location: /campaigns');
			die ('Failed to complete request, database issue. Please report submit a report at http://support.opheme.com if the problem persists. Redirecting to Campaigns...');
			
		}
	
	}
	
	header('Location: /campaigns');
	die ('No input info. Redirecting to Campaigns...');

?>