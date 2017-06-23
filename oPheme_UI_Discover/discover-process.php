<?php

	require_once("php/common.php");
	require_once("php/protect.php");
	
	if (!empty($_POST)) {
		
		$data = $_POST;

		$opheme->discover_setData($data);
		
		if (@$_POST['discover_id'] == 0) { //create discover
			
			$ok = $opheme->discover_create();
			
		} elseif (@is_numeric($_POST['discover_id'])) {
			
			if (@$_POST['discover_delete'] == 1) {
				
				//delete
				$ok = $opheme->discover_delete();
				
			} else {
				
				//edit
				$ok = $opheme->discover_edit();
				
			}
			
		}
		
		if ($ok === true) {
			
			header('Location: /discovers');
			die ('Discover has been successfully created. Redirecting to Discovers...');
			
		} else {
			
			header('Location: /discovers');
			die ('Failed to complete request, database issue. Please report submit a report at http://support.opheme.com if the problem persists. Redirecting to Discovers...');
			
		}
	
	}
	
	header('Location: /discovers');
	die ('No input info. Redirecting to Discovers...');

?>