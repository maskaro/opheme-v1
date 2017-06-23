<?php

	require_once("php/common.php");
	require_once("php/protect.php");

?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo brand; ?> | Account</title>
		<!-- CSS -->
		<?php include("html_inc/css.inc"); ?>
		<!-- JS -->
		<?php include('html_inc/js.inc'); ?>
	</head>
	<body>
		<!-- Sticky Navigation -->
		<?php include("html_inc/navigation.inc"); ?>
		<!-- End Sticky Navigation -->
		<div id="dashboard" class="container account">
			<div class="row-fluid">
				<div class="span6 offset3">
					<div id="sma-container">
						<h4>Social Media Authorization Status</h4>
						<?php echo (isset($_SESSION['twitter_ok'])?'<div class="row-fluid"><div class="span12"><div class="alert alert-success">Successfully authorized with Twitter.</div></div></div>':''); unset($_SESSION['twitter_ok']); ?>
						<?php echo (isset($_SESSION['twitter_message'])?'<div class="row-fluid"><div class="span12"><div class="alert alert-error">' . $_SESSION['twitter_message'] . '</div></div></div>':''); unset($_SESSION['twitter_message']); ?>
						<div class="row-fluid">
							<div class="span12">
								<label for="twitter-authorize">Twitter</label>
								<?php
									if ($_SESSION['twitter_authorized']) {
										echo '<div class="alert alert-success">Authorized. If authorization expires, this message will change. If you wish to change Twitter account, please click the button below.</div>';
									} else {
										echo '<div class="alert alert-error">You must authorize with Twitter before using ' . brand . '.</div>';
									}
								?>
								<a href="/twitter_authorize_request"><img src="/img/twitter_auth_lighter.png" alt="Authorize with Twitter"/></a>
							</div>
						</div>
					</div>
					<div id="account-container">
						<h4>Account Settings</h4>
						<?php echo (isset($_SESSION['account_ok'])?'<div class="row-fluid"><div class="span12"><div class="alert alert-success">Successfully saved account info.</div></div></div>':''); unset($_SESSION['account_ok']); ?>
						<?php echo (($_SESSION['showCreationModule'] == false && @$_SESSION['trial_expired'] == false)?'<div class="row-fluid"><div class="span12"><div class="alert alert-error">You must update all Account fields before using ' . brand . '.</div></div></div>':''); ?>
						<?php echo ((@$_SESSION['trial_expired'] == true)?'<div class="row-fluid"><div class="span12"><div class="alert alert-error">Trial period has expired! Please consider subscribing to continue enjoying ' . brand . '.</div></div></div>':''); ?>
						<form id="account" action="/account-process" method="post">
							<div class="row-fluid">
								<div class="span6">
									<label for="first-name">First Name</label>
									<input class="input-block-level" type="text" required="required" name="first-name" placeholder="first name" value="<?php echo @$_SESSION['user']['firstname']; ?>" />
								</div>
								<div class="span6">
									<label for="last-name">Last Name</label>
									<input class="input-block-level" type="text" required="required" name="last-name" placeholder="last name" value="<?php echo @$_SESSION['user']['lastname']; ?>" />
								</div>
							</div>
							<div class="row-fluid">
								<div class="span12">
									<label for="business-type">Phone number</label>
									<input class="input-block-level" type="text" required="required" name="phone" placeholder="phone number" value="<?php echo @$_SESSION['user']['phone']; ?>" />
								</div>
							</div>
							<div class="row-fluid">
								<div class="span12">
									<label for="business-type">Business name</label>
									<input class="input-block-level" type="text" required="required" name="business-type" placeholder="business name" value="<?php echo @$_SESSION['user']['business_type']; ?>" />
								</div>
							</div>
							<div class="row-fluid">
								<div class="span12">
									<label for="business-www">Business webpage</label>
									<input class="input-block-level" type="text" required="required" name="business-www" placeholder="http://www.yourbusiness.co.uk" value="<?php echo @$_SESSION['user']['business_www']; ?>" />
								</div>
							</div>
							<div class="row-fluid">
								<div class="span12">
									<label for="email-address">Email Address</label>
									<span class="input-block-level"><?php echo @$_SESSION['user']['email']; ?></span>
								</div>
							</div>
							<div class="row-fluid">
								<div class="span12">
									<label for="old-password">Current Password (required to make changes)</label>
									<input class="input-block-level" type="password" name="old-password" value="" required="required" />
								</div>
							</div>
							<div class="row-fluid">
								<div class="span6">
									<label for="new-password">New Password</label>
									<input class="input-block-level" type="password" name="new-password" id="new-password" value="" />
								</div>
								<div class="span6">
									<label for="confirm-password">Confirm New Password</label>
									<input class="input-block-level" type="password" name="confirm-password" value="" />
								</div>
							</div>
							<div class="alert alert-info alert-block">
								If you change your password, you will be IMMEDIATELY logged out! You will then need to login using your new password.
							</div>
							<div class="row-fluid">
								<div class="span12">
									<input class="btn btn-large btn-block btn-primary" type="submit" value="Save Changes" />
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php include('html_inc/footer.inc'); ?>
		<!-- End Main -->
		<!-- Campaign Editor -->
		<?php if ($_SESSION['showCreationModule']) {
			include('html_inc/campaign_editor.inc');
			include('html_inc/campaign_editor_scripts.inc');
			include('html_inc/campaigns_scripts.inc'); } ?>
		<script type="text/javascript">
			
			$("form#account").validate({
				errorClass: "alert alert-error",
				validClass: "alert alert-success",
				//validation rules
				rules: {
					'first-name': 'required',
					'last-name': 'required',
					phone: 'required',
					'business-type': 'required',
					'business-www': {
						required: true,
						url: true
					},
					'old-password': 'required',
					'confirm-password': {
						equalTo: "#new-password"
					}
				}
			});
			
		</script>
	</body>
</html>