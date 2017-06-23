<?php

	require_once("php/common.php");
	require_once("php/protect.php");
	
	if ($_SESSION['showCreationModule'] == false) {
		header('Location: /account');
		die('Account information missing. Redirecting to Account...');
	}
	
	$redirect = true; $allow = array(3, 4, 5, 6);
	if ($_SESSION['user']['subscription'] == 0 && $_SESSION['trial_expired'] == false) $redirect = false;
	if (in_array($_SESSION['user']['subscription'], $allow)) $redirect = false;
	
	if ($redirect == true){
		$_SESSION['account_message'] = "Please upgrade your subscription to access the Campaigns Management area.";
		header("Location: /dashboard");
		die("Please upgrade your subscription to access the Campaigns Management area. Redirecting to Account ...");
	}

?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo brand; ?> | Campaigns</title>
		<!-- CSS -->
		<?php include("html_inc/css.inc"); ?>
		<!-- JS -->
		<?php include('html_inc/js.inc'); ?>
	</head>
	<body>
		<!-- Sticky Navigation -->
		<?php include("html_inc/navigation.inc"); ?>
		<!-- End Sticky Navigation -->
		<div id="dashboard" class="container">
			<?php /*<div class="alert alert-info alert-block">
				<button type="button" class="close">&times;</button>
				<h4>New features added!</h4>
				<br />The Campaign Editor has been transformed into a Wizard, so please edit all your Campaigns (mouse over the Campaign below and click the Pencil icon) making sure they match the new requirements. Thank you!
			</div>*/ ?>
			<?php echo (isset($_SESSION['campaign_create_ok'])?'<div class="alert alert-success">Campaign was successfully created.</div>':''); unset($_SESSION['campaign_create_ok']); ?>
			<?php echo (isset($_SESSION['campaign_edit_ok'])?'<div class="alert alert-success">Campaign was successfully edited.</div>':''); unset($_SESSION['campaign_edit_ok']); ?>
			<?php echo (isset($_SESSION['campaign_delete_ok'])?'<div class="alert alert-success">Campaign was successfully deleted.</div>':''); unset($_SESSION['campaign_delete_ok']); ?>
			<?php echo (isset($_SESSION['campaign_message'])?'<div class="alert alert-error">' . $_SESSION['campaign_message'] . '</div>':''); unset($_SESSION['campaign_message']); ?>
			<div class="row-fluid">
				<div id="campaign-list" class="span6">
					<div id="campaign-wrapper">
						<table id="campaign-table" class="table table-hover">
							<thead>
								<tr>
									<th>Active</th>
									<th>Campaign Name</th>
									<th>Responses Sent</th>
								</tr>
							</thead>
							<tbody height="400">
								<?php $opheme->campaign_getAllStats(); ?>
							</tbody>
						</table>
					</div>
				</div>
				<div id="campaign-preview" class="span6">
					<div class="row-fluid preview-placeholder loader" style="z-index: 1000; background-color: white; margin-top: 0px; opacity: 1">
						<div class="span12">
							<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
							<img src="/img/loader.gif" alt="loader" />
							<br />
							<h2>Loading Messages, Please Wait</h2>
						</div>
					</div>
					<div class="row-fluid preview-placeholder icon">
						<div class="span12">
							<i class="icon-search icon-large"></i>
							<h2>Preview</h2>
							<h4>Click on one of your Campaigns on the left in order to pause/resume its messages or view, edit or delete it.</h4>
						</div>
					</div>
					<?php $opheme->campaign_getAllMaps(); ?>
				</div>
			</div>
		</div>
		<?php include('html_inc/footer.inc'); ?>
		<!-- Campaign Editor -->
		<?php include('html_inc/campaign_editor.inc'); ?>
		<?php include('html_inc/campaign_editor_scripts.inc'); ?>
		<?php include('html_inc/campaigns_scripts.inc'); ?>
		<!-- End Main -->
	</body>
</html>