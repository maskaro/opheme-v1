<?php

	require_once("php/common.php");
	require_once("php/protect.php");
	
	if (!isset($_SESSION['user']['admin']) || intval($_SESSION['user']['admin']) !== 1) {
		header('Location: /dashboard');
		die('Unauthorized. Redirecting to Dashboard...');
	}

?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo brand; ?> | Admin</title>
		<!-- CSS -->
		<?php include("html_inc/css.inc"); ?>
		<!-- JS -->
		<?php include('html_inc/js.inc'); ?>
		<script type="text/javascript">
			
			var geocoder = new google.maps.Geocoder();
			
			function codeLatLng(lat, lng, container) {
				
				var latlng = new google.maps.LatLng(lat, lng);
				
				geocoder.geocode({'latLng': latlng}, function(results, status) {
					if (status == google.maps.GeocoderStatus.OK) {
						if (results[0]) {
							$(container).html(results[0].formatted_address);
						}
					} else {
						var callThis = function() {
							codeLatLng(lat, lng, container);
						}
						window.setTimeout(callThis, 3000);
						//$(container).html("Geocoder failed due to: " + status);
					}
				});
				
			}
			
		</script>
	</head>
	<body>
		<!-- Sticky Navigation -->
		<?php include("html_inc/navigation.inc"); ?>
		<!-- End Sticky Navigation -->
		<div id="dashboard" class="container account">
			<?php echo (isset($_SESSION['admin_ok'])?'<div class="alert alert-success">' . $_SESSION['admin_ok'] . '</div>':''); unset($_SESSION['admin_ok']); ?>
			<?php echo (isset($_SESSION['admin_message'])?'<div class="alert alert-error">' . $_SESSION['admin_message'] . '</div>':''); unset($_SESSION['admin_message']); ?>
			<div class="row-fluid">
				<div class="span12">
					<div id="account-container">
						<h4>System Overview</h4>
						<div class="row-fluid" style="max-height: 500px; overflow: auto">
							<?php $opheme->getSystemOverview(); ?>
						</div>
					</div>
				</div>
			</div>
			<br />
			<div class="row-fluid">
				<div class="span12">
					<div id="account-container">
						<h4>Generate a new Account</h4>
						<div class="row-fluid" style="max-height: 500px; overflow: auto">
							<?php $opheme->system_admin_tokensGetAll(); ?>
						</div>
					</div>
				</div>
			</div>
			<br />
			<div class="row-fluid">
				<div class="span12">
					<div id="account-container">
						<h4>Clients on system</h4>
						<div class="row-fluid" style="max-height: 500px; overflow: auto">
							<?php $opheme->system_admin_clientsGetAllOverview(); ?>
						</div>
					</div>
				</div>
			</div>
			<!--<div class="row-fluid">
				<div class="span12">
					<div id="account-container">
						<h4><?php echo brand; ?> Backend PHP Log (from running Module jobs) - last 50 lines - should contain info on what each module is currently doing and any massive errors that might occurs</h4>
						<div class="row-fluid" style="max-height: 500px; overflow: auto">
							<?php /*$output = shell_exec('cat /var/log/apache2/php_errors.log'); echo "<pre>$output</pre>";*/ ?>
						</div>
					</div>
				</div>
			</div>-->
		</div>
		<?php include('html_inc/footer.inc'); ?>
	</body>
</html>