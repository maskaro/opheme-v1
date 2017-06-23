<?php

	require_once("php/common.php");
	require_once("php/protect.php");
	
	if (!isset($_SESSION['user']['admin']) || intval($_SESSION['user']['admin']) !== 1 ||
		!isset($_SESSION['user']['reseller']) || intval($_SESSION['user']['reseller']) !== 1) {
		header('Location: /account');
		die('Unauthorized. Redirecting to Account...');
	}
	
	if (!empty($_POST)) {
		
		$data = $_POST;
		$return = false;
		
		switch ($data['action']) {
			case 'resetDiscoverMessages':
				$return = $opheme->system_admin_discoverResetMessages($data['discover_id']);
				break;
			case 'suspendDiscover':
				$return = $opheme->system_admin_discoverSuspend($data['discover_id']);
				break;
			case 'resumeDiscover':
				$return = $opheme->system_admin_discoverResume($data['discover_id']);
				break;
			case 'deleteDiscover':
				$return = $opheme->system_admin_discoverDelete($data['discover_id']);
				break;
			case 'createToken':
				$return = $user->system_admin_createToken($data['client_email']);
				break;
			case 'deleteToken':
				$return = $user->system_admin_removeToken($data['token_id']);
				break;
			case 'activateClient':
				$return = $opheme->system_admin_activateClient($data['client_email']);
				if (isset($_SESSION['send_to_email'])) {
					$user->register_send_ok_email($_SESSION['send_to_email']);
					unset($_SESSION['send_to_email']);
				}
				break;
			case 'changeClientSub':
				$return = $opheme->system_admin_changeClientSub($data['client_email'], $data['sub_id']);
				break;
			case 'resetClientTrial':
				$return = $opheme->system_admin_resetClientTrial($data['client_email']);
				break;
			case 'suspendClient':
				$return = $opheme->system_admin_suspendClient($data['client_email']);
				break;
			case 'resumeClient':
				$return = $opheme->system_admin_resumeClient($data['client_email']);
				break;
			case 'deleteClient':
				$return = $opheme->system_admin_removeClient($data['client_email']);
				break;
			default:
				break;
		}
		
		if (substr_count($_SERVER['HTTP_REFERER'], 'reseller') > 0) $redir = '/reseller';
		else $redir = '/admin';
		
		if ($return == true) {
			header('Location: ' . $redir);
			die ('Admin task successfully completed. Redirecting back...');
		} else {
			header('Location: ' . $redir);
			die ('Admin task failed. Redirecting back...');
		}
		
	}
	
	header('Location: ' . $redir);
	die ('No input data. Redirecting back...');

?>