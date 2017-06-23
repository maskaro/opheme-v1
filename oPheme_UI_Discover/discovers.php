<?php

	require_once("php/common.php");
	require_once("php/protect.php");
	
	if ($_SESSION['showCreationModule'] == false) {
		header('Location: /account');
		die('Account information missing. Redirecting to Account...');
	}
	
	$redirect = true; $allow = array(1, 2, 5, 6);
	if ($_SESSION['user']['subscription'] == 0 && $_SESSION['trial_expired'] == false) $redirect = false;
	if (in_array($_SESSION['user']['subscription'], $allow)) $redirect = false;
	
	if ($redirect == true){
		$_SESSION['account_message'] = "Please upgrade your subscription to access the Discovers Management area.";
		header("Location: /dashboard");
		die("Please upgrade your subscription to access the Discovers Management area. Redirecting to Account ...");
	}

?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo brand; ?> | Discovers</title>
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
			<?php echo (isset($_SESSION['discover_create_ok'])?'<div class="alert alert-success">Discovers was successfully created.</div>':''); unset($_SESSION['discover_create_ok']); ?>
			<?php echo (isset($_SESSION['discover_edit_ok'])?'<div class="alert alert-success">Discovers was successfully edited.</div>':''); unset($_SESSION['discover_edit_ok']); ?>
			<?php echo (isset($_SESSION['discover_delete_ok'])?'<div class="alert alert-success">Discovers was successfully deleted.</div>':''); unset($_SESSION['discover_delete_ok']); ?>
			<?php echo (isset($_SESSION['discover_message'])?'<div class="alert alert-error">' . $_SESSION['discover_message'] . '</div>':''); unset($_SESSION['discover_message']); ?>
			<div class="row-fluid">
				<div id="campaign-list" class="span6">
					<div id="campaign-wrapper">
						<table id="campaign-table" class="table table-hover">
							<thead>
								<tr>
									<th>Active</th>
									<th>Discover Name</th>
									<th>Messages Discovered</th>
								</tr>
							</thead>
							<tbody height="400">
								<?php $opheme->discover_getAllStats(); ?>
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
							<h4>Click on one of your Discovers on the left in order to pause/resume its messages or view, edit or delete it.</h4>
						</div>
					</div>
					<?php $opheme->discover_getAllMaps(); ?>
				</div>
			</div>
		</div>
		<?php include('html_inc/footer.inc'); ?>
		<!-- Discover Editor -->
		<?php include('html_inc/discover_editor.inc'); ?>
		<?php include('html_inc/discover_editor_scripts.inc'); ?>
		<?php include('html_inc/discovers_scripts.inc'); ?>
		<!-- End Main -->
	</body>
</html>