<?php
	
	//check account suspension status
	if (intval($account_suspended) == 1) {
		trigger_error('Account suspended. Skipped.');
		exit;
	}
	
	//check account time limit
	if (intval($allowance['account_time_limit']) > 0) {
		$until = strtotime('+' . $allowance['account_time_limit'], strtotime($account_created)); $now = strtotime('now');
		if ($now >= $until) {
			trigger_error('Account trial expired. Skipped.');
			exit;
		}
	}
	
	//check account job TIME LIMIT
	$continue = false;
	if (intval($allowance['time_limit']) > 0) {
		$until = strtotime('+' . $allowance['time_limit'], strtotime($job_added));
		$now = strtotime('now');
		if ($until >= $now) $continue = true;
	} else { $continue = true; }
	if ($continue == false) {
		trigger_error('Job time limit expired. Skipped.');
		exit;
	}
	
	//if message limit has been reached, don't run job
	if ($allowance['messages_limit'] > 0) {
		if ($message_count >= $allowance['messages_limit']) {
			trigger_error('Job message limit reached. Skipped.');
			exit;
		}
	}

?>