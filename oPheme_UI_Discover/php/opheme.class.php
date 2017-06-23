<?php

class opheme extends db {
	
	//plugin general settings
	protected $settings;
	
	//specific info
	protected $_d;
	
	//mongo
	protected $m;
	
	function __construct($_db_cred) {
		
		global $settings;
		
		$this->settings = $settings;
		
		if (class_exists('MongoClient')) $this->m = new MongoClient();
		
		parent::__construct($_db_cred);
		
	}
	
	function __destruct() {
		
		parent::__destruct();
		
	}
	
	function log($table, $action) {
		
		$query = "INSERT INTO opheme_logs.$table (user_id, action) values (:user_id, :action)";
		$query_params = array(
			':user_id' => $_SESSION['user']['email'],
			':action' => $action
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
	}
	
	function purge_jobs($table) {
		
		$query = "SELECT email, subscription FROM secure_login.users";
		$query_params = array();
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		$error = false;
		
		if($stmt->rowCount() > 1) {
		
			$rows_all = $stmt->fetchAll();
			
			foreach ($rows_all as $row) {
				
				$allowance = $this->system_admin_getUserAllowance($row['subscription']);
				$jobs_count = $this->system_admin_getDiscoversCount($row['email']);
				$over = $jobs_count - $allowance['job_limit'];
				
				if ($over > 0) {
					
					$query = "DELETE FROM $table WHERE user_id = '" . $row['email'] . "' ORDER BY added ASC LIMIT $over";
					$query_params = array();
					
					try {
						$stmt = $this->db->prepare($query);
						$result = $stmt->execute($query_params);
					} catch(PDOException $ex) {
						$this->error_message($ex);
					}
					
					if ($stmt->rowCount() == 0) {
						
						$error = true;
						
					} else {
						
						$this->log('admin_operations', 'Removed older discovers for Client ' . $row['email'] . ' due to subscription downgrading.');
						
					}
					
				}
				
			}
			
		}
		
		return $error;
		
	}
	
	function system_admin_getDiscoversCount($email = false, $overview = false) {
		
		if ($overview === false) {
		
			$query = "SELECT count(*) as discs FROM discovers";
			$query_params = array();
			
		} else {
			
			$query = "SELECT count(*) as discs, sum(message_count) as message_sum FROM discovers";
			$query_params = array();
			
		}
		
		if ($email !== false) {
			$query .= " WHERE user_id = :email";
			$query_params = array(':email' => $email);
		}
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() == 1) {
		
			$row = $stmt->fetch();
			
			if ($overview === false) return $row['discs'];
			else return $row;
			
		}
		
		return false;
		
	}
	
	function system_admin_getCampaignsCount($email = false, $overview = false) {
		
		if ($overview === false) {
		
			$query = "SELECT count(*) as camps FROM campaigns";
			$query_params = array();
			
		} else {
			
			$query = "SELECT count(*) as camps, sum(message_count) as message_sum FROM campaigns";
			$query_params = array();
			
		}
		
		if ($email !== false) {
			$query .= " WHERE user_id = :email";
			$query_params = array(':email' => $email);
		}
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() == 1) {
		
			$row = $stmt->fetch();
			
			if ($overview === false) return $row['camps'];
			else return $row;
			
		}
		
		return false;
		
	}
	
	function system_admin_getAllSubscriptionTypesArray() {
		
		$query = "SELECT name, id FROM sub_limits";
		$query_params = array();
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() > 0) {
		
			$rows = $stmt->fetchAll();
			
			return $rows;
			
		}
		
	}
	
	//get all clients on system
	function system_admin_clientsGetAllOverview($reseller = false) {
		
		$query = "SELECT * FROM secure_login.users";
		$query_params = array();
		
		if ($reseller == true) {
			$query .= ' where from_company = :email';
			$query_params = array(':email' => $_SESSION['user']['email']);
		}
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() > 0) {
			
			echo '<div class="span12">
					<table class="table">
						<thead>
							<tr>
								<th>Active</th>
								<th>Last Login</th>
								<th>Email</th>
								<th>Name</th>
								<th>Phone</th>
								<th>Business</th>
								<th>Joined</th>
								<th>Subscription</th>
								<th>Discovers</th>
								<th>Campaigns</th>
								<th>Tasks</th>
							</tr>
						</thead>
						<tbody>';
			
			$rows_all = $stmt->fetchAll();
			
			foreach($rows_all as $row) {
				
				
				$sub = $this->system_admin_getUserAllowance($row['subscription']);
				$discs = $this->system_admin_getDiscoversCount($row['email']);
				$camps = $this->system_admin_getCampaignsCount($row['email']);
				
				echo '<tr>
						<td>' . (intval($row['suspended']) == 0?'<span style="color: green">Yes</span>':'<span style="color: red">No</span>') . '</td>
						<td>' . ($row['last_login'] == '0000-00-00 00:00:00'?'Never':$row['last_login']) . '</td>
						<td>' . $row['email'] . '</td>
						<td>' . $row['firstname'] . ' ' . $row['lastname'] . '</td>
						<td>' . $row['phone'] . '</td>
						<td><a href="' . $row['business_www'] . '" target="_blank">' . $row['business_type'] . '</a></td>
						<td>' . $row['created'] . '</td>
						<td>';
				
				$subscriptions = $this->system_admin_getAllSubscriptionTypesArray();
				
				echo '		<form action="/admin-process" method="post">
								<input type="hidden" name="client_email" value="' . $row['email'] . '" />
								<input type="hidden" name="action" value="changeClientSub" />
								<select name="sub_id" onchange="this.form.submit()" style="width: 110px">';
				
				foreach($subscriptions as $subscription) {
					
					echo '			<option value="' . $subscription['id'] . '"' . ($subscription['id']==$sub['id']?' selected':'') . '>' . $subscription['name'] . '</option>';
					
				}
				
				echo '			</select>
							</form>';
				
				echo '	</td>
						<td onclick="hideAllBut(\'jobs_\', \'jobs_discovers_\', \'' . str_replace(array('@', '.', '-', '+', '_'), '', $row['email']) . '\')"><a>' . $discs . '</a></td>
						<td onclick="hideAllBut(\'jobs_\', \'jobs_campaigns_\', \'' . str_replace(array('@', '.', '-', '+', '_'), '', $row['email']) . '\')"><a>' . $camps . '</a></td>
						<td>
							<form action="/admin-process" method="post">
								<input type="hidden" name="client_email" value="' . $row['email'] . '" />'
								. ($_SESSION['user']['email'] == $row['email']?
										'<strong>*YOU*</strong>'
										:''
								)
								. '<div class="btn-group">'
									. ($_SESSION['user']['email'] != $row['email']?
											(intval($row['suspended']) == 0?
											'<button class="btn" type="submit" name="action" value="suspendClient" style="display: inline">Suspend</button>':
											'<button class="btn" type="submit" name="action" value="resumeClient" style="display: inline">Resume</button>'
											):''
									)
									. ($sub['name'] == 'Trial'?
									   '<button class="btn" type="submit" name="action" value="resetClientTrial" style="display: inline">Reset Trial</button>':
									   ''
									)
									. (strlen($row['code']) == 1?
											'':
											'<button class="btn" type="submit" name="action" value="activateClient" style="display: inline">Activate</button>'
									)
									. ($_SESSION['user']['email'] == $row['email']?
											'':
											'<button class="btn" type="submit" name="action" value="deleteClient" style="display: inline">Remove</button>'
									)
								. '</div>
							</form>
						</td>
					</tr>
				';
					
				$query = "SELECT * FROM discovers WHERE user_id = :email";
				$query_params = array(':email' => $row['email']);
				
				try {
					$stmt = $this->db->prepare($query);
					$result = $stmt->execute($query_params);
				} catch(PDOException $ex) {
					$this->error_message($ex);
				}
				
				if($stmt->rowCount() > 0) {
					
					echo '<tr class="jobs_discovers_' . str_replace(array('@', '.', '-', '+', '_'), '', $row['email']) . '" style="display: none; padding: 10px">
						<td colspan="11" style="border: none">
							<h4><strong>Discovers</strong></h4>';
					
					$script = '<script type="text/javascript">';
					
					echo '<table class="table" style="margin-left: 10px; border: 2px solid">
							<thead>
								<tr>
									<th>Active</th>
									<th>Name</th>
									<th>Keyword</th>
									<th>Address of Centre</th>
									<th>Radius</th>
									<th>Received Messages</th>
									<th>Allowance</th>
									<th>Tasks</th>
								</tr>
							</thead>
							<tbody>';
					
					$rows_all_jobs = $stmt->fetchAll();
					
					foreach($rows_all_jobs as $row_job) {
						
						$script .= 'codeLatLng("' . $row_job['centre_lat'] . '", "' . $row_job['centre_lng'] . '", "#discover_address_' . $row_job['id'] . '");' . PHP_EOL;
						
						echo '<tr>
								<td>' . (intval($row_job['suspended']) == 0?'<span style="color: green">Yes</span>':'<span style="color: red">No</span>') . '</td>
								<td>' . $row_job['name'] . '</td>
								<td>' . $row_job['filter'] . '</td>
								<td><span id="discover_address_' . $row_job['id'] . '"></span></td>
								<td>' . $row_job['radius'] . ' miles</td>
								<td>' . $row_job['message_count'] . '</td>
								<td>' . ((intval($row_job['messages_limit']) > 99999999999999)?'Unlimited':$row_job['messages_limit']) . ' messages' . (intval($row_job['time_limit']) > 0?', ' . $row_job['time_limit']:'') . '</td>
								<td>
									<form action="/admin-process" method="post">
										<input type="hidden" name="discover_id" value="' . $row_job['id'] . '" />
										<div class="btn-group">'
											. (intval($row_job['message_count']) > 0?'<button class="btn" type="submit" name="action" value="resetDiscoverMessages" style="display: inline">Reset</button>':'')
											. (intval($row_job['suspended']) == 0?'<button class="btn" type="submit" name="action" value="suspendDiscover" style="display: inline">Suspend</button>':'<button class="btn" type="submit" name="action" value="resumeDiscover" style="display: inline">Resume</button>') .
											'<button class="btn" type="submit" name="action" value="deleteDiscover" style="display: inline">Delete</button>
										</div>
									</form>
								</td>
							</tr>
						';
						
					}
					
					echo '	</tbody>
						</table>';
						
					$script .= '</script>';
					
					echo $script;
					
					echo '</td>
						</tr>';
					
				}
				
				$query = "SELECT * FROM campaigns WHERE user_id = :email";
				$query_params = array(':email' => $row['email']);
				
				try {
					$stmt = $this->db->prepare($query);
					$result = $stmt->execute($query_params);
				} catch(PDOException $ex) {
					$this->error_message($ex);
				}
				
				if($stmt->rowCount() > 0) {
					
					echo '<tr class="jobs_campaigns_' . str_replace(array('@', '.', '-', '+', '_'), '', $row['email']) . '" style="display: none; padding: 10px">
						<td colspan="11" style="border: none">
							<h4><strong>Campaigns</strong></h4>';
					
					$script = '<script type="text/javascript">';
					
					echo '<table class="table" style="margin-left: 10px; border: 2px solid">
							<thead>
								<tr>
									<th>Active</th>
									<th>Name</th>
									<th>Category</th>
									<th>Keyword</th>
									<th>Address of Centre</th>
									<th>Radius</th>
									<th>Sent Messages</th>
									<th>Allowance</th>
									<th>Tasks</th>
								</tr>
							</thead>
							<tbody>';
					
					$rows_all_jobs = $stmt->fetchAll();
						
					foreach($rows_all_jobs as $row_job) {
						
						$script .= 'codeLatLng("' . $row_job['centre_lat'] . '", "' . $row_job['centre_lng'] . '", "#campaign_address_' . $row_job['id'] . '");' . PHP_EOL;
						
						echo '<tr>
								<td>' . (intval($row_job['suspended']) == 0?'<span style="color: green">Yes</span>':'<span style="color: red">No</span>') . '</td>
								<td>' . $row_job['name'] . '</td>
								<td>' . $row_job['category'] . '</td>
								<td>' . $row_job['filter'] . '</td>
								<td><span id="campaign_address_' . $row_job['id'] . '"></span></td>
								<td>' . $row_job['radius'] . ' miles</td>
								<td>' . $row_job['message_count'] . '</td>
								<td>' . ((intval($row_job['messages_limit']) > 99999999999999)?'Unlimited':$row_job['messages_limit']) . ' messages' . (intval($row_job['time_limit']) > 0?', ' . $row_job['time_limit']:'') . '</td>
								<td>
									<form action="/admin-process" method="post">
										<input type="hidden" name="campaign_id" value="' . $row_job['id'] . '" />
										<div class="btn-group">'
											. (intval($row_job['message_count']) > 0?'<button class="btn" type="submit" name="action" value="resetCampaignMessages" style="display: inline">Reset</button>':'')
											. (intval($row_job['suspended']) == 0?'<button class="btn" type="submit" name="action" value="suspendCampaign" style="display: inline">Suspend</button>':'<button class="btn" type="submit" name="action" value="resumeCampaign" style="display: inline">Resume</button>') .
											'<button class="btn" type="submit" name="action" value="deleteCampaign" style="display: inline">Delete</button>
										</div>
									</form>
								</td>
							</tr>
						';
						
					}
					
					echo '	</tbody>
						</table>';
					
					$script .= '</script>';
					
					echo $script;
					
					echo '</td>
						</tr>';
					
				}
				
			}
			
			echo '		</tbody>
					</table>
				</div>';
			
			return true;
			
		}
		
		echo '<div class="span12">
				<span class="input-block-level">No Cients on system.</span>
			</div>';
		
		return false;
		
	}
	
	function system_admin_discoverSuspend($id) {
		
		$query = "UPDATE discovers SET suspended = 1 WHERE id = :id";
		$query_params = array(
			':id' => $id
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if ($stmt->rowCount() == 1) {
			$_SESSION['admin_ok'] = 'Successfully suspended Discover with ID ' . $id . '.';
			$this->log('admin_operations', $_SESSION['admin_ok']);
			return true;
		}
		
		$_SESSION['admin_message'] = 'Failed to suspend Discover with ID ' . $id . '. Database error. Please submit a report at http://support.opheme.com if this error persists.';
		$this->log('admin_operations_fails', $_SESSION['admin_message']);
		return false;
		
	}
	
	function system_admin_discoverResume($id) {
		
		$query = "UPDATE discovers SET suspended = 0 WHERE id = :id";
		$query_params = array(
			':id' => $id
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if ($stmt->rowCount() == 1) {
			$_SESSION['admin_ok'] = 'Successfully resumed Discover with ID ' . $id . '.';
			$this->log('admin_operations', $_SESSION['admin_ok']);
			return true;
		}
		
		$_SESSION['admin_message'] = 'Failed to resume Discover with ID ' . $id . '. Database error. Please submit a report at http://support.opheme.com if this error persists.';
		$this->log('admin_operations_fails', $_SESSION['admin_message']);
		return false;
		
	}
	
	function system_admin_changeClientSub($email, $sub_id) {
		
		$query = "UPDATE secure_login.users SET subscription = :sub_id WHERE email = :email";
		$query_params = array(
			':sub_id' => $sub_id,
			':email' => $email
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if ($stmt->rowCount() == 1) {
			
			$allowance = $this->system_admin_getUserAllowance($sub_id);
			
			$query = "UPDATE campaigns SET time_limit = :time_limit, messages_limit = :messages_limit WHERE user_id = :email";
			$query_params = array(
				':time_limit' => $allowance['time_limit'],
				':messages_limit' => $allowance['messages_limit'],
				':email' => $email
			);
			
			try {
				$stmt = $this->db->prepare($query);
				$result = $stmt->execute($query_params);
			} catch(PDOException $ex) {
				$this->error_message($ex);
			}
			
			$query = "UPDATE discovers SET time_limit = :time_limit, messages_limit = :messages_limit WHERE user_id = :email";
			$query_params = array(
				':time_limit' => $allowance['time_limit'],
				':messages_limit' => $allowance['messages_limit'],
				':email' => $email
			);
			
			try {
				$stmt = $this->db->prepare($query);
				$result = $stmt->execute($query_params);
			} catch(PDOException $ex) {
				$this->error_message($ex);
			}
			
			$_SESSION['admin_ok'] = 'Successfully changed subscription level for Client with Email ' . $email . '.';
			$this->log('admin_operations', $_SESSION['admin_ok']);
			return true;
		}
		
		$_SESSION['admin_message'] = 'Failed to change subscription level for Client with Email ' . $email . '. Database error. Please submit a report at http://support.opheme.com if this error persists.';
		$this->log('admin_operations_fails', $_SESSION['admin_message']);
		return false;
		
	}
	
	function system_admin_activateClient($id) {
		
		$query = "UPDATE secure_login.users SET code = 0 WHERE email = :id";
		$query_params = array(
			':id' => $id
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if ($stmt->rowCount() == 1) {
			$_SESSION['admin_ok'] = 'Successfully activated Client with Email ' . $id . '.';
			$_SESSION['send_to_email'] = $id;
			$this->log('admin_operations', $_SESSION['admin_ok']);
			return true;
		}
		
		$_SESSION['admin_message'] = 'Failed to activate Client with Email ' . $id . '. Database error. Please submit a report at http://support.opheme.com if this error persists.';
		$this->log('admin_operations_fails', $_SESSION['admin_message']);
		return false;
		
	}
	
	function system_admin_resetClientTrial($id) {
		
		$query = "UPDATE secure_login.users SET created = :time WHERE email = :id";
		$query_params = array(
			':time' => date('Y-m-d H:i:s'),
			':id' => $id
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if ($stmt->rowCount() == 1) {
			$_SESSION['admin_ok'] = 'Successfully reset Client Trial with Email ' . $id . '.';
			$this->log('admin_operations', $_SESSION['admin_ok']);
			return true;
		}
		
		$_SESSION['admin_message'] = 'Failed to reset Client Trial with Email ' . $id . '. Database error. Please submit a report at http://support.opheme.com if this error persists.';
		$this->log('admin_operations_fails', $_SESSION['admin_message']);
		return false;
		
	}
	
	function system_admin_suspendClient($id) {
		
		$query = "UPDATE secure_login.users SET suspended = 1 WHERE email = :id";
		$query_params = array(
			':id' => $id
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if ($stmt->rowCount() == 1) {
			$_SESSION['admin_ok'] = 'Successfully suspended Client with Email ' . $id . '.';
			$this->log('admin_operations', $_SESSION['admin_ok']);
			return true;
		}
		
		$_SESSION['admin_message'] = 'Failed to suspend Client with Email ' . $id . '. Database error. Please submit a report at http://support.opheme.com if this error persists.';
		$this->log('admin_operations_fails', $_SESSION['admin_message']);
		return false;
		
	}
	
	function system_admin_resumeClient($id) {
		
		$query = "UPDATE secure_login.users SET suspended = 0 WHERE email = :id";
		$query_params = array(
			':id' => $id
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if ($stmt->rowCount() == 1) {
			$_SESSION['admin_ok'] = 'Successfully suspended Client with Email ' . $id . '.';
			$this->log('admin_operations', $_SESSION['admin_ok']);
			return true;
		}
		
		$_SESSION['admin_message'] = 'Failed to suspend Client with Email ' . $id . '. Database error. Please submit a report at http://support.opheme.com if this error persists.';
		$this->log('admin_operations_fails', $_SESSION['admin_message']);
		return false;
		
	}
	
	//get all clients on system
	function system_admin_removeClient($email) {
		
		if ($_SESSION['user']['email'] == $email) {
			$_SESSION['admin_message'] = 'You cannot delete yourself!';
			$this->log('admin_operations_fails', $_SESSION['admin_message']);
			return false;
		}
		
		$query = "DELETE FROM secure_login.users WHERE email = :email";
		$query_params = array(':email' => $email);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if ($result == false) {
			$_SESSION['admin_message'] = 'Failed to delete Client with Email ' . $email . '. Database error. Please submit a report at http://support.opheme.com if this error persists.';
			$this->log('admin_operations_fails', $_SESSION['admin_message']);
			return false;
		}
		
		$query = "DELETE FROM discovers WHERE user_id = :email";
		$query_params = array(':email' => $email);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if ($result == false) {
			$_SESSION['admin_message'] = 'Deleted Client with Email ' . $email . ', but failed to delete associated Discovers on system. Database error. Please submit a report at http://support.opheme.com if this error persists.';
			$this->log('admin_operations_fails', $_SESSION['admin_message']);
			return false;
		}
		
		$query = "DELETE FROM discovers WHERE user_id = :email";
		$query_params = array(':email' => $email);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if ($result == false) {
			$_SESSION['admin_message'] = 'Deleted Client with Email ' . $email . ' and associated Discovers, but failed to delete associated Discovers on system. Database error. Please submit a report at http://support.opheme.com if this error persists.';
			$this->log('admin_operations_fails', $_SESSION['admin_message']);
			return false;
		}
		
		$query = "DELETE FROM twitter_keys WHERE user_id = :email";
		$query_params = array(':email' => $email);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if ($result == false) {
			$_SESSION['admin_message'] = 'Deleted Client with Email ' . $email . ' and associated Discovers / Discovers on system, but failed to delete Twitter Tokens. Database error. Please submit a report at http://support.opheme.com if this error persists.';
			$this->log('admin_operations_fails', $_SESSION['admin_message']);
			return false;
		}
		
		$_SESSION['admin_ok'] = 'Successfully removed Client with Email ' . $email . '.';
		$this->log('admin_operations', $_SESSION['admin_ok']);
		return true;
		
	}
	
	//gets user allowance based on sub_id
	function system_admin_getUserAllowance($sub) {
		
		$query = "SELECT * FROM sub_limits WHERE id = :user_sub";
		$query_params = array(':user_sub' => $sub);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() == 1) {
			
			$row = $stmt->fetch();
			
			return $row;
			
		}
		
		return false;
		
	}
	
	//get all tokens
	function system_admin_tokensGetAll($reseller = false) {
		
		$query = "SELECT * FROM secure_login.tokens";
		$query_params = array();
		
		if ($reseller == true) {
			$query .= ' WHERE from_company = :email';
			$query_params = array(':email' => $_SESSION['user']['email']);
		}
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() > 0) {
			
			echo '<div class="span12">
					<div style="max-height: 420px; overflow: auto">
						<table class="table">
							<thead>
								<tr>
									<th>Email</th>
									<th>Tasks</th>
								</tr>
							</thead>
							<tbody>';
			
			$rows_all = $stmt->fetchAll();
			
			foreach($rows_all as $row) {
				
				echo '<tr>
						<td>' . $row['email'] . '</td>
						<td><form action="/admin-process" method="post"><input type="hidden" name="token_id" value="' . $row['id'] . '" /><div class="btn-group"><button class="btn" type="submit" name="action" value="deleteToken" style="display: inline">Delete</button></div></form></td>
					</tr>
				';
				
			}
			
			echo '			</tbody>
						</table>
					</div>
					<form action="/admin-process" method="post"><input type="hidden" name="action" value="createToken" /><input type="text" name="client_email" placeholder="email@service.com" required="required" /> <input class="btn" type="submit" value="Create New Account" /></form>
				</div>';
			
			return true;
			
		}
		
		echo '<div class="span12">
				<span class="input-block-level">No Unused Accounts on system.</span>
				<span class="input-block-level"><form action="/admin-process" method="post"><input type="hidden" name="action" value="createToken" /><input type="text" name="client_email" placeholder="email@service.com" required="required" /> <input class="btn" type="submit" value="Create New Account" /></form></span>
			</div>';
		
		return false;
		
	}
	
	function system_admin_discoverDelete($id) {
		
		$query = "DELETE FROM discovers WHERE id = :id";
		$query_params = array(':id' => $id);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if ($stmt->rowCount() == 1) {
			$_SESSION['admin_ok'] = 'Successfully deleted discover ID ' . $id . '.';
			$this->log('admin_operations', $_SESSION['admin_ok']);
			return true;
		}
		
		$_SESSION['admin_message'] = 'Failed to delete discover ID ' . $id . '. Database error. Please submit a report at http://support.opheme.com if this error persists.';
		$this->log('admin_operations_fails', $_SESSION['admin_message']);
		return false;
		
	}
	
	function system_admin_discoverResetMessages($id) {
		
		$query = "UPDATE discovers SET message_count = 0 WHERE id = :id";
		$query_params = array(
			':id' => $id
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if ($stmt->rowCount() == 1) {
			$_SESSION['admin_ok'] = 'Successfully reset message allowance for discover ID ' . $id . '.';
			$this->log('admin_operations', $_SESSION['admin_message']);
			return true;
		}
		
		$_SESSION['admin_message'] = 'Failed to reset message allowance for discover ID ' . $id . '. Database error. Please submit a report at http://support.opheme.com if this error persists.';
		$this->log('admin_operations_fails', $_SESSION['admin_message']);
		return false;
		
	}
	
	//get all discs on system
	function system_admin_discoverGetAllOverview($reseller = false) {
		
		if ($reseller == true) {
			
			$query = "SELECT email FROM secure_login.users WHERE from_company = :email ";
			$query_params = array(':email' => $_SESSION['user']['email']);
			
		} else {
			
			$query = "SELECT email FROM secure_login.users";
			$query_params = array();
			
		}
			
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() > 0) {
			
			$script = '<script type="text/javascript">';
					
			echo '<div class="span12">
					<div>
						<table class="table">
							<thead>
								<tr>
									<th>Active</th>
									<th>ID</th>
									<th>Name</th>
									<th>Keyword</th>
									<th>Address of Centre</th>
									<th>Radius</th>
									<th>Received Messages</th>
									<th>Allowance</th>
									<th>Company</th>
									<th>Tasks</th>
								</tr>
							</thead>
							<tbody>';
			
			$emails_all = $stmt->fetchAll();
			$displayed = false;
			
			foreach($emails_all as $email) {
				
				$query = "SELECT * FROM discovers WHERE user_id = :email";
				$query_params = array(':email' => $email['email']);
				
				try {
					$stmt = $this->db->prepare($query);
					$result = $stmt->execute($query_params);
				} catch(PDOException $ex) {
					$this->error_message($ex);
				}
				
				if($stmt->rowCount() > 0) {
					
					$rows_all = $stmt->fetchAll();
					$displayed = true;
					
					foreach($rows_all as $row) {
						
						$script .= 'codeLatLng("' . $row['centre_lat'] . '", "' . $row['centre_lng'] . '", "#discover_address_' . $row['id'] . '");' . PHP_EOL;
						
						echo '<tr>
								<td>' . (intval($row['suspended']) == 0?'<span style="color: green">Yes</span>':'<span style="color: red">No</span>') . '</td>
								<td>' . $row['id'] . '</td>
								<td>' . $row['name'] . '</td>
								<td>' . $row['filter'] . '</td>
								<td><span id="discover_address_' . $row['id'] . '"></span></td>
								<td>' . $row['radius'] . ' miles</td>
								<td>' . $row['message_count'] . '</td>
								<td>' . ((intval($row['messages_limit']) > 99999999999999)?'Unlimited':$row['messages_limit']) . ' messages' . (intval($row['time_limit']) > 0?', ' . $row['time_limit']:'') . '</td>
								<td>' . $row['user_id'] . '</td>
								<td>
									<form action="/admin-process" method="post">
										<input type="hidden" name="discover_id" value="' . $row['id'] . '" />
										<div class="btn-group">'
											. (intval($row['message_count']) > 0?'<button class="btn" type="submit" name="action" value="resetDiscoverMessages" style="display: inline">Reset</button>':'')
											. (intval($row['suspended']) == 0?'<button class="btn" type="submit" name="action" value="suspendDiscover" style="display: inline">Suspend</button>':'<button class="btn" type="submit" name="action" value="resumeDiscover" style="display: inline">Resume</button>') .
											'<button class="btn" type="submit" name="action" value="deleteDiscover" style="display: inline">Delete</button>
										</div>
									</form>
								</td>
							</tr>
						';
						
					}
				
				}
				
			}
			
			if ($displayed == false) {
				
				echo '<tr><td colspan="11">No Discovers yet on System.</td>';
				
			}
			
			echo '			</tbody>
						</table>
					</div>
				</div>';
				
			$script .= '</script>';
			
			echo $script;
			
		} else {
			
			echo '<div class="span12">
				<span class="input-block-level">No Clients on system.</span>
			</div>';
			
		}
		
	}
	
	//get all camps on system
	function system_admin_campaignGetAllOverview($reseller = false) {
		
		if ($reseller == true) {
			
			$query = "SELECT email FROM secure_login.users WHERE from_company = :email ";
			$query_params = array(':email' => $_SESSION['user']['email']);
			
		} else {
			
			$query = "SELECT email FROM secure_login.users";
			$query_params = array();
			
		}
			
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() > 0) {
			
			$script = '<script type="text/javascript">';
					
			echo '<div class="span12">
					<div>
						<table class="table">
							<thead>
								<tr>
									<th>Active</th>
									<th>ID</th>
									<th>Name</th>
									<th>Category</th>
									<th>Keyword</th>
									<th>Address of Centre</th>
									<th>Radius</th>
									<th>Sent Messages</th>
									<th>Allowance</th>
									<th>Company</th>
									<th>Tasks</th>
								</tr>
							</thead>
							<tbody>';
			
			$emails_all = $stmt->fetchAll();
			$displayed = false;
			
			foreach($emails_all as $email) {
				
				$query = "SELECT * FROM campaigns WHERE user_id = :email";
				$query_params = array(':email' => $email['email']);
				
				try {
					$stmt = $this->db->prepare($query);
					$result = $stmt->execute($query_params);
				} catch(PDOException $ex) {
					$this->error_message($ex);
				}
				
				if($stmt->rowCount() > 0) {
					
					$rows_all = $stmt->fetchAll();
					$displayed = true;
					
					foreach($rows_all as $row) {
						
						$script .= 'codeLatLng("' . $row['centre_lat'] . '", "' . $row['centre_lng'] . '", "#campaign_address_' . $row['id'] . '");' . PHP_EOL;
						
						echo '<tr>
								<td>' . (intval($row['suspended']) == 0?'<span style="color: green">Yes</span>':'<span style="color: red">No</span>') . '</td>
								<td>' . $row['id'] . '</td>
								<td>' . $row['name'] . '</td>
								<td>' . $row['category'] . '</td>
								<td>' . $row['filter'] . '</td>
								<td><span id="campaign_address_' . $row['id'] . '"></span></td>
								<td>' . $row['radius'] . ' miles</td>
								<td>' . $row['message_count'] . '</td>
								<td>' . ((intval($row['messages_limit']) > 99999999999999)?'Unlimited':$row['messages_limit']) . ' messages' . (intval($row['time_limit']) > 0?', ' . $row['time_limit']:'') . '</td>
								<td>' . $row['user_id'] . '</td>
								<td>
									<form action="/admin-process" method="post">
										<input type="hidden" name="campaign_id" value="' . $row['id'] . '" />
										<div class="btn-group">'
											. (intval($row['message_count']) > 0?'<button class="btn" type="submit" name="action" value="resetCampaignMessages" style="display: inline">Reset</button>':'')
											. (intval($row['suspended']) == 0?'<button class="btn" type="submit" name="action" value="suspendCampaign" style="display: inline">Suspend</button>':'<button class="btn" type="submit" name="action" value="resumeCampaign" style="display: inline">Resume</button>') .
											'<button class="btn" type="submit" name="action" value="deleteCampaign" style="display: inline">Delete</button>
										</div>
									</form>
								</td>
							</tr>
						';
						
					}
				
				}
				
			}
			
			if ($displayed == false) {
				
				echo '<tr><td colspan="11">No Campaigns yet on System.</td>';
				
			}
			
			echo '			</tbody>
						</table>
					</div>
				</div>';
				
			$script .= '</script>';
			
			echo $script;
			
		} else {
			
			echo '<div class="span12">
				<span class="input-block-level">No Clients on system.</span>
			</div>';
			
		}
		
	}
	
	function system_admin_getUserBusiness($email) {
		
		$query = "SELECT business_type FROM secure_login.users WHERE email = :email";
		$query_params = array(':email' => $email);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() == 1) {
		
			$row = $stmt->fetch();
			
			return $row['business_type'];
			
		}
		
		return false;
		
	}
	
	function system_getUserAllowance() {
		
		$query = "SELECT * FROM sub_limits WHERE id = :user_sub";
		$query_params = array(':user_sub' => $_SESSION['user']['subscription']);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() == 1) {
			
			$row = $stmt->fetch();
			
			return $row;
			
		}
		
		return false;
		
	}
	
	function system_twitter_didUserAuthorize() {
		
		$query = "SELECT 1 FROM twitter_keys WHERE user_id = :email";
		$query_params = array(':email' => $_SESSION['user']['email']);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() == 1) { return true; }
		
		return false;
		
	}
	
	function system_twitter_getUserToken() {
		
		$email = $_SESSION['user']['email'];
		
		$query = "SELECT * FROM twitter_keys WHERE user_id = :email";
		$query_params = array(':email' => $email);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() == 1) {
			
			$row = $stmt->fetch();
			
			$token = array(
				'token' => $row['token'],
				'token_secret' => $row['token_secret']
			);
			
			return $token;
			
		}
		
		return false;
		
	}
	
	function system_twitter_saveUserAccessToken($token) {
		
		if ($this->system_twitter_getUserToken() != false) { //replace token if one exists
			$query = "DELETE FROM twitter_keys WHERE user_id = :email";
			$query_params = array(':email' => $_SESSION['user']['email']);
			
			try {
				$stmt = $this->db->prepare($query);
				$result = $stmt->execute($query_params);
			} catch(PDOException $ex) {
				$this->error_message($ex);
			}
		}
		
		$query = "INSERT INTO twitter_keys (user_id, token, token_secret) VALUES (:email, :token, :token_secret)";
		$query_params = array(
			':email' => $_SESSION['user']['email'],
			':token' => $token['oauth_token'],
			':token_secret' => $token['oauth_token_secret']
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if (!$result) {
			
			$_SESSION['twitter_message'] = 'Database error. If this issue persists, please report submit a report at http://support.opheme.com.';
			return false;
			
		}
		
		return true;
		
	}
	
	function discover_setData($data) {
		
		if (isset($data['disc_info'])) $this->_d = $data['disc_info'];
		else $this->_d = $data;
		
	}
	
	//attempts to validate camp specs, returns true or false
	protected function discover_isValid() {
		
		//account job count limits
		$allowance = $this->system_getUserAllowance();
		$job_count = $this->system_admin_getDiscoversCount($_SESSION['user']['email']);
		
		if (intval($allowance['discover_job_limit']) > 0 && intval($allowance['discover_job_limit']) == $job_count) {
			$_SESSION['discover_message'] = 'You have reached your Discover creation limit. Please remove an existing Discover or upgrade your subscription.';
			return false;
		}
		
		//camp form specs
		$disc = $this->_d;
		
		//discover name
		if (!isset($disc['discover_name'])) {
			$_SESSION['discover_message'] = 'Discover must have a name.';
			return false;
		}
		
		//discover filter
		if (isset($disc['discover_filter']) && strlen($disc['discover_filter']) > 0)
			if (strlen($disc['discover_filter']) < 1) {
				$_SESSION['discover_message'] = 'Discover keyword should have a minimum of 1 character.';
				return false;
			}
		
		if (isset($disc['discover_filter_ex']) && strlen($disc['discover_filter_ex']) > 0) {
			$filter_ex = explode(' ', $disc['discover_filter_ex']);
			foreach ($filter_ex as $keyword)
				if (strlen($keyword) < 1) {
					$_SESSION['discover_message'] = 'Discover exclusion keywords should have a minimum of 1 character.';
					return false;
				}
		}
		
		//at least one week day selected
		//if (!isset($disc['discover_days']) || count($disc['discover_days']) == 0) {
			//$_SESSION['discover_message'] = 'You must select at least one week day.';
			//return false;
		//}
		
		if (isset($disc['discover_time_start']) && strlen($disc['discover_time_start']) > 0) {
			if (!$this->isTimeValid($disc['discover_time_start'])) {
				$_SESSION['discover_message'] = 'Discover must have a valid start time.';
				return false;
			}
			if (!isset($disc['discover_time_end']) || strlen($disc['discover_time_end']) == 0) {
				$_SESSION['discover_message'] = 'Discover must have a valid end time.';
				return false;
			}
		}
		
		if (isset($disc['discover_time_end']) && strlen($disc['discover_time_end']) > 0) {
			if (!$this->isTimeValid($disc['discover_time_end'])) {
				$_SESSION['discover_message'] = 'Discover must have a valid end time.';
				return false;
			}
			if (!isset($disc['discover_time_start']) || strlen($disc['discover_time_start']) == 0) {
				$_SESSION['discover_message'] = 'Discover must have a valid start time.';
				return false;
			}
		}
		
		if (isset($disc['discover_date_start']) && strlen($disc['discover_date_start']) > 0) {
			if (!$this->isDateValid($disc['discover_date_start'])) {
				$_SESSION['discover_message'] = 'Discover must have a valid start date.';
				return false;
			}
			if (!isset($disc['discover_date_end']) || strlen($disc['discover_date_end']) == 0) {
				$_SESSION['discover_message'] = 'Discover must have a valid end date.';
				return false;
			}
		}
		
		if (isset($disc['discover_date_end']) && strlen($disc['discover_date_end']) > 0) {
			if (!$this->isDateValid($disc['discover_date_end'])) {
				$_SESSION['discover_message'] = 'Discover must have a valid end date.';
				return false;
			}
			if (!isset($disc['discover_date_start']) || strlen($disc['discover_date_start']) == 0) {
				$_SESSION['discover_message'] = 'Discover must have a valid start date.';
				return false;
			}
		}
		
		if (isset($disc['discover_time_start']) && isset($disc['discover_time_end']) && strlen($disc['discover_time_start']) > 0 && strlen($disc['discover_time_end']) > 0)
			if (date('H:i', strtotime($disc['discover_time_start'])) > date('H:i', strtotime($disc['discover_time_end']))) {
				$_SESSION['discover_message'] = 'Start time cannot be after the end time.';
				return false;
			}
		
		if (isset($disc['discover_date_start']) && isset($disc['discover_date_end']) && strlen($disc['discover_date_start']) > 0 && strlen($disc['discover_date_end']) > 0)
			if (date('Y-m-d', strtotime($disc['discover_date_start'])) > date('Y-m-d', strtotime($disc['discover_date_end']))) {
				$_SESSION['discover_message'] = 'Start date cannot be after the end date.';
				return false;
			}
		
		if (isset($disc['discover_date_start']) && strlen($disc['discover_date_start']) > 0)
			if (date('Y-m-d', strtotime(time())) > date('Y-m-d', strtotime($disc['discover_date_start']))) {
				$_SESSION['discover_message'] = 'Start date cannot be in the past.';
				return false;
			}
		
		if (isset($disc['discover_date_end']) && strlen($disc['discover_date_end']) > 0)
			if (date('Y-m-d', strtotime(time())) > date('Y-m-d', strtotime($disc['discover_date_end']))) {
				$_SESSION['discover_message'] = 'End date cannot be in the past.';
				return false;
			}
		
		//latitude and longitude have to be numeric
		if (!isset($disc['discover_centre_lat']) || !isset($disc['discover_centre_lng']) || !is_numeric($disc['discover_centre_lat']) || !is_numeric($disc['discover_centre_lng'])) {
			$_SESSION['discover_message'] = 'Latitude and Longitude coordinates must be set and valid.';
			return false;
		}
		
		//numeric radius
		if (!isset($disc['discover_radius']) || !is_numeric($disc['discover_radius'])) {
			$_SESSION['discover_message'] = 'Radius must be set and valid.';
			return false;
		}
		
		//max 1mi
		if (((floatval($disc['discover_radius']) - 10) > 0) || floatval($disc['discover_radius']) < 0) {
			$_SESSION['discover_message'] = 'Radius must be between 0.1 and 10 miles.';
			return false;
		}
		
		//true or false
		return true;
		
	}
	
	//validates 00:00 -> 23:59
	protected function isTimeValid($time) {
	    return (is_object(DateTime::createFromFormat('H:i', $time)) || is_object(DateTime::createFromFormat('H:i:s', $time)));
	}
	
	protected function isDateValid($date) {
	    return (is_object(DateTime::createFromFormat('m/d/Y', $date)) || is_object(DateTime::createFromFormat('Y-m-d', $date)));
	}
	
	//create discover
	function discover_create() {
		
		//is camp valid?
		if ($this->discover_isValid()) {
			
			$allowance = $this->system_getUserAllowance();
			
			if (isset($this->_d['discover_date_start']) && strlen($this->_d['discover_date_start']) > 0) {
				$start_date = DateTime::createFromFormat('m/d/Y', $this->_d['discover_date_start']);
				$start_date_db = $start_date->format('Y-m-d');
			} else $start_date_db = '0000-00-00';
			if (isset($this->_d['discover_date_end']) && strlen($this->_d['discover_date_end']) > 0) {
				$end_date = DateTime::createFromFormat('m/d/Y', $this->_d['discover_date_end']);
				$end_date_db = $end_date->format('Y-m-d');
			} else $end_date_db = '0000-00-00';
			
			if (isset($this->_d['discover_time_start']) && strlen($this->_d['discover_time_start']) > 0) {
				$start_time = $this->_d['discover_time_start'];
			} else $start_time = '00:00:00';
			if (isset($this->_d['discover_time_end']) && strlen($this->_d['discover_time_end']) > 0) {
				$end_time = $this->_d['discover_time_end'];
			} else $end_time = '00:00:00';
			
			if (isset($this->_d['discover_days'])) $days = implode(',', $this->_d['discover_days']);
			else $days = '';
			
			$query = "INSERT INTO discovers (user_id, name, filter, filter_ex, centre_lat, centre_lng, radius, weekdays, start_time, end_time, start_date, end_date, messages_limit, time_limit, since_id, message_count) VALUES (:email, :name, :filter, :filter_ex, :centre_lat, :centre_lng, :radius, :weekdays, :start_time, :end_time, :start_date, :end_date, :messages_limit, :time_limit, :since_id, :message_count)";
			$query_params = array(
				':email' => $_SESSION['user']['email'],
				':name' => $this->_d['discover_name'],
				':filter' => $this->_d['discover_filter'],
				':filter_ex' => $this->_d['discover_filter_ex'],
				':centre_lat' => $this->_d['discover_centre_lat'],
				':centre_lng' => $this->_d['discover_centre_lng'],
				':radius' => $this->_d['discover_radius'],
				':weekdays' => $days,
				':start_time' => $start_time,
				':end_time' => $end_time,
				':start_date' => $start_date_db,
				':end_date' => $end_date_db,
				':messages_limit' => $allowance['messages_limit'],
				':time_limit' => $allowance['time_limit'],
				':since_id' => 	0,
				':message_count' => 0
			);
			
			try {
				$stmt = $this->db->prepare($query);
				$result = $stmt->execute($query_params);
			} catch(PDOException $ex) {
				$this->error_message($ex);
			}
			
			if (!$result) {
				
				$_SESSION['discover_message'] = 'Database error. If this issue persists, please report submit a report at http://support.opheme.com.';
				return false;
				
			}
			
			$_SESSION['discover_create_ok'] = true;
			return true;
		
		}
		
		return false;
		
	}
	
	//edit discover
	function discover_edit() {
		
		//is camp valid?
		if ($this->discover_isValid()) {
			
			$allowance = $this->system_getUserAllowance();
			
			if (isset($this->_d['discover_date_start']) && strlen($this->_d['discover_date_start']) > 0) {
				$start_date = DateTime::createFromFormat('Y-m-d', $this->_d['discover_date_start']);
				if (!is_object($start_date)) $start_date = DateTime::createFromFormat('m/d/Y', $this->_d['discover_date_start']);
				$start_date_db = $start_date->format('Y-m-d');
			} else $start_date_db = '0000-00-00';
			if (isset($this->_d['discover_date_end']) && strlen($this->_d['discover_date_end']) > 0) {
				$end_date = DateTime::createFromFormat('Y-m-d', $this->_d['discover_date_end']);
				if (!is_object($end_date)) $end_date = DateTime::createFromFormat('m/d/Y', $this->_d['discover_date_end']);
				$end_date_db = $end_date->format('Y-m-d');
			} else $end_date_db = '0000-00-00';
			
			if (isset($this->_d['discover_time_start']) && strlen($this->_d['discover_time_start']) > 0) {
				$start_time = $this->_d['discover_time_start'];
			} else $start_time = '00:00:00';
			if (isset($this->_d['discover_time_end']) && strlen($this->_d['discover_time_end']) > 0) {
				$end_time = $this->_d['discover_time_end'];
			} else $end_time = '00:00:00';
			
			if (isset($this->_d['discover_days'])) $days = implode(',', $this->_d['discover_days']);
			else $days = '';
			
			$query_params = array(
				':name' => $this->_d['discover_name'],
				':filter' => $this->_d['discover_filter'],
				':filter_ex' => $this->_d['discover_filter_ex'],
				':centre_lat' => $this->_d['discover_centre_lat'],
				':centre_lng' => $this->_d['discover_centre_lng'],
				':radius' => $this->_d['discover_radius'],
				':weekdays' => $days,
				':start_time' => $start_time,
				':end_time' => $end_time,
				':start_date' => $start_date_db,
				':end_date' => $end_date_db,
				':messages_limit' => $allowance['messages_limit'],
				':time_limit' => $allowance['time_limit'],
				':id' => $this->_d['discover_id']
			);
			
			$query = "UPDATE discovers SET name = :name, filter = :filter, filter_ex = :filter_ex, centre_lat = :centre_lat, centre_lng = :centre_lng, radius = :radius, weekdays = :weekdays, start_time = :start_time, end_time = :end_time, start_date = :start_date, end_date = :end_date, messages_limit = :messages_limit, time_limit = :time_limit WHERE id = :id";
			
			try {
				$stmt = $this->db->prepare($query);
				$result = $stmt->execute($query_params);
			} catch(PDOException $ex) {
				$this->error_message($ex);
			}
			
			if (!$result) {
				
				$_SESSION['discover_message'] = 'Database error. If this issue persists, please report submit a report at http://support.opheme.com.';
				return false;
				
			}
			
			$_SESSION['discover_edit_ok'] = true;
			return true;
		
		}
		
		return false;
		
	}
	
	function discover_delete() {
		
		$query = "DELETE FROM discovers WHERE id = :id";
		$query_params = array(':id' => $this->_d['discover_id']);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() == 1) {
			
			$_SESSION['discover_delete_ok'] = true;
			return true;
			
		}
		
		$_SESSION['discover_message'] = 'Database error. If this issue persists, please report submit a report at http://support.opheme.com.';
		return false;
		
	}
	
	//return full camp specs
	function discover_getSpecs() {
		
		$query = "SELECT * FROM discovers WHERE id = :id";
		$query_params = array(':id' => $this->_d['id']);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() == 1) {
			
			$row = $stmt->fetch();
			return $row;
			
		}
		
		return false;
		
	}
	
	//get all camps on system
	function discover_getAllStats() {
		
		$query = "SELECT suspended, id, name, filter, filter_ex, centre_lat, centre_lng, radius, weekdays, start_time, end_time, start_date, end_date, message_count FROM discovers WHERE user_id = :user_id";
		$query_params = array(':user_id' => $_SESSION['user']['email']);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() > 0) {
			
			$rows_all = $stmt->fetchAll();
			
			foreach($rows_all as $row) {
				
				echo '<tr class="campaign" id="discover_' . $row['id'] . '" json=\'' . json_encode($row, JSON_HEX_APOS) . '\'>
						<td id="discover_' . $row['id'] . '_status">' . (intval($row['suspended']) == 0?'<span style="color: green">Yes</span>':'<span style="color: red">No</span>') . '</td>
						<td class="campaign-name">' . $row['name'] . '</td>
						<td class="campaign-responses"><span id="discover_' . $row['id'] . '_count">' . $row['message_count'] . '</span> <span class="pull-right"><i title="Suspend Task" class="icon-pause icon-large text-warning"' . ($row['suspended'] == 1?' style="display: none"':'') . '></i> <i title="Resume Task" class="icon-play icon-large text-success"' . ($row['suspended'] == 0?' style="display: none"':'') . '></i> <i title="Edit Task" class="icon-pencil icon-large text-success"></i> <i title="Remove Task"class="icon-remove icon-large text-error"></i> <i title="View Task" class="icon-search icon-large text-info"></i></span></td>
					</tr>
				';
				
			}
			
			return true;
			
		}
		
		echo '<tr class="campaign">
				<td class="campaign-name" colspan="3">Create a new Discover!</td>
			</tr>
		';
		
		return false;
		
	}
	
	//get all camps on system
	function discover_getAllMaps() {
		
		$query = "SELECT id, centre_lat, centre_lng FROM discovers WHERE user_id = :user_id";
		$query_params = array(':user_id' => $_SESSION['user']['email']);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		if($stmt->rowCount() > 0) {
			
			$rows_all = $stmt->fetchAll();
			
			foreach($rows_all as $row) {
				
				echo '<div class="campaign-map" style="display: none;" id="map_discover_' . $row['id'] . '"></div>
				<script>
					
					oph_discover_' . $row['id'] . ' = new $.oPhemeUI($("#map_discover_' . $row['id'] . '"), {
						timeout: 15000 //run every 15 sec
					});
					
					coords_discover_' . $row['id'] . ' = { lat: parseFloat("' . $row['centre_lat'] . '").toFixed(7), lng: parseFloat("' . $row['centre_lng'] . '").toFixed(7) };
					
					map_discover_' . $row['id'] . ' = oph_discover_' . $row['id'] . '.map({
						api: "gmaps",
						settings: {
							options: {
								map_centre: coords_discover_' . $row['id'] . ',
								zoom: 9
							}
						}
					});
					
					oph_discover_' . $row['id'] . '.getDiscSpecs("' . $row['id'] . '");
					oph_discover_' . $row['id'] . '.startDisc();
					oph_discover_' . $row['id'] . '.startDiscQueue();
					
					$.extend(maps_json, {map_discover_' . $row['id'] . ' : {handle: map_discover_' . $row['id'] . ', id: ' . $row['id'] . '}});
					
				</script>
				';
				
			}
			
			return true;
			
		}
		
		return false;
		
	}
	
	//check for new camp messages
	function discover_getNewMessages() {
		
		$id = strval($this->_d['id']);
		$ref = strval($this->_d['refresh']);
		
		$db_set = $this->m->opheme_ui;
		// select a collection (analogous to a relational database's table)
		$coll_ts = $db_set->timestamps_discovers;
		
		$tmp = $coll_ts->findOne(array("discover_id" => $id));
		if (isset($tmp["last_id"]) && $ref == '0') $last = $tmp["last_id"];
		else {
			$last = 0;
			$coll_ts->update(array("discover_id" => $id), array("discover_id" => $id, "last_id" => "0"), array("upsert" => true));
		}
		
		//select a database
		$db = $this->m->jobs;
		// select a collection (analogous to a relational database's table)
		$collection = $db->discovers;
		
		$db_tweets = $this->m->messages;
		// select a collection (analogous to a relational database's table)
		$coll = $db_tweets->tweets;
		
		$cursor = $collection->find(array('discover_id' => $id));
		$return = array();
		$one = array();
		
		$max = $last;
			
		foreach ($cursor as $doc) { //$doc is an assoc array
			
			if ($doc['tweet_id'] > $max) $max = $doc['tweet_id'];
			
			if ($doc['tweet_id'] > $last) {
				
				$doc_full = $coll->findOne(array('id_str' => $doc['tweet_id']));
				$one = array(
					'user' => array(
						'profile_image_url' => $doc_full['user']['profile_image_url'],
						'screen_name' => $doc_full['user']['screen_name'],
						'id' => $doc_full['user']['id_str']
					),
					'text' => $doc_full['text'],
					'created_at' => $doc_full['created_at'],
					'geo' => $doc_full['geo']
				);
				array_push($return, $one);
				
			}
			
		}
		
		if (count($return) > 0) {
			
			$coll_ts->update(array("discover_id" => $id), array("discover_id" => $id, "last_id" => $max), array("upsert" => true));
			
			return $return;
		
		}
		
		return 0;
		
	}
	
	function discover_pause() {
		
		$id = strval($this->_d['id']);
		
		$query = "UPDATE discovers SET suspended = 1 WHERE id = :id";
		$query_params = array(
			':id' => $id
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		return $stmt->rowCount();
		
	}
	
	function discover_unPause() {
		
		$id = strval($this->_d['id']);
		
		$query = "UPDATE discovers SET suspended = 0 WHERE id = :id";
		$query_params = array(
			':id' => $id
		);
		
		try {
			$stmt = $this->db->prepare($query);
			$result = $stmt->execute($query_params);
		} catch(PDOException $ex) {
			$this->error_message($ex);
		}
		
		return $stmt->rowCount();
		
	}
	
	function discover_twitterFollow() {
		
		$user_toFollow = strval($this->_d['user_id']);
		$user_current = $_SESSION['user']['email'];
		
		$token = $this->system_twitter_getUserToken($user_current);
		$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $token['token'], $token['token_secret']);
		
		$content = $connection->post('friendships/create', array('user_id' => $user_toFollow, 'follow' => true));
		$content_arr = $this->objectToArray($content);
		
		$message = $connection->http_code . ': ' . $connection->url;
		if (isset($content_arr_search['errors'])) {
			foreach($content_arr_search['errors'] as $error)
				$message .= ' / ' . implode(', ', $error);
			$message .= ' / ' . implode(', ', $params);
		}
		trigger_error($message);
		
		if ($connection->http_code == 200) {
			
			$query = "INSERT INTO opheme_twitter_follows.follow_forward (opheme_user_id, twitter_user_id) VALUES (:email, :twitter_id)";
			$query_params = array(
				':email' => $user_current,
				':twitter_id' => $user_toFollow,
			);
			
			try {
				$stmt = $this->db->prepare($query);
				$result = $stmt->execute($query_params);
			} catch(PDOException $ex) {
				$this->error_message($ex);
			}
			
			return $stmt->rowCount();
		}
		
		else return 0;
		
	}
	
	function objectToArray($d) {
		if (is_object($d)) {
			// Gets the properties of the given object
			// with get_object_vars function
			$d = get_object_vars($d);
		}
		if (is_array($d)) {
			/*
			* Return array converted to object
			* Using __FUNCTION__ (Magic constant)
			* for recursive call
			*/
			return array_map(__METHOD__, $d);
		}
		else {
			// Return array
			return $d;
		}
	}
	
	function getLastLine($file) {
		
		$line = '';

		$f = fopen($file, 'r');
		$cursor = -1;
		
		fseek($f, $cursor, SEEK_END);
		$char = fgetc($f);
		
		/**
		 * Trim trailing newline chars of the file
		 */
		while ($char === "\n" || $char === "\r") {
			fseek($f, $cursor--, SEEK_END);
			$char = fgetc($f);
		}
		
		/**
		 * Read until the start of file or first newline char
		 */
		while ($char !== false && $char !== "\n" && $char !== "\r") {
			/**
			 * Prepend the new char
			 */
			$line = $char . $line;
			fseek($f, $cursor--, SEEK_END);
			$char = fgetc($f);
		}
		
		return $line;
		
	}
	
	function getLoadPercentage($cpu_count, $load) { return floor($load * 100 / $cpu_count); }
	function getMemoryMB($memory) { return floor(($memory / 1024) * 100) / 100; }
	function getMemoryGB($memory) { return floor(($memory / 1024 / 1024) * 100) / 100; }
	
	function getSystemOverview() {
		
		$loads_line = str_replace('  ', ' ', $this->getLastLine(logs_path . 'load.log'));
		$memory_line = $this->getLastLine(logs_path . 'memory.log');
		$cpu_count = file_get_contents(logs_path . 'cpu.count');
		$discovers = $this->system_admin_getDiscoversCount(false, true);
		$campaigns = $this->system_admin_getCampaignsCount(false, true);
		
		list($time, $one, $five, $onefive) = explode(' ', $loads_line);
		$loads_text = '<div>'
							.	'Last CPU check has been done: <strong>' . date('l jS \of F Y h:i:s A', $time) . '</strong>.<br>'
							.	'CPU load last minute average: <strong>' . $this->getLoadPercentage($cpu_count, $one) . '%</strong>.<br>'
							.	'CPU load last 5 minutes average: <strong>' . $this->getLoadPercentage($cpu_count, $five) . '%</strong>.<br>'
							.	'CPU load last 15 minutes average: <strong>' . $this->getLoadPercentage($cpu_count, $onefive) . '%</strong>.'
						. '</div>';
		
		list($time, $total, $used, $free) = explode(' ', $memory_line);
		$memory_text = '<div>'
							.	'Last RAM check has been done: <strong>' . date('l jS \of F Y h:i:s A', $time) . '</strong>.<br>'
							.	'RAM Total: <strong>' . $this->getMemoryMB($total) . 'MB</strong>, <strong>' . $this->getMemoryGB($total) . 'GB</strong>.<br>'
							.	'RAM Used: <strong>' . $this->getMemoryMB($used) . 'MB</strong>, <strong>' . $this->getMemoryGB($used) . 'GB</strong>.<br>'
							.	'RAM Free: <strong>' . $this->getMemoryMB($free) . 'MB</strong>, <strong>' . $this->getMemoryGB($free) . 'GB</strong>.'
						. '</div>';
						
		$overview_text = '<div class="span12">
							Total Discovers: <strong>' . $discovers['discs'] . '</strong>. Total Discover Messages: <strong>' . $discovers['message_sum'] . '</strong>.<br>
							Total Campaigns: <strong>' . $campaigns['camps'] . '</strong>. Total Campaign Messages: <strong>' . $campaigns['message_sum'] . '</strong>.<br>
							<br>
							' . $loads_text . '<br>' . $memory_text . '
						</div>';
						
		echo $overview_text;
		
	}
	
}