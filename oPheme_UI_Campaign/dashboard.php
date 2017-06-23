<?php

	require_once("php/common.php");
	require_once("php/protect.php");

?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo brand; ?> | Dashboard</title>
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
					<div id="account-container">
						<h4>Dashboard</h4>
						<?php echo (isset($_SESSION['account_message'])?'<div class="row-fluid"><div class="span12"><div class="alert alert-error">' . $_SESSION['account_message'] . '</div></div></div>':''); unset($_SESSION['account_message']); ?>
						<?php if ($_SESSION['twitter_authorized'] == false) echo '<div class="row-fluid"><div class="span12"><div class="alert alert-error">You must <a href="/account">Authorize</a> with Twitter before using ' . brand . '.</div></div></div>'; ?>
						<?php echo (($_SESSION['showCreationModule'] == false && @$_SESSION['trial_expired'] == false)?'<div class="row-fluid"><div class="span12"><div class="alert alert-error">You must update all <a href="/account">Account</a> details before using ' . brand . '.</div></div></div>':''); ?>
						<?php echo ((@$_SESSION['trial_expired'] == true)?'<div class="row-fluid"><div class="span12"><div class="alert alert-error">Trial period has expired! Please consider subscribing to continue enjoying ' . brand . '.</div></div></div>':''); ?>
						<div class="row-fluid">
							<div class="well text-left">
								Discovers: <?php echo $user->getDiscoverCount(); ?><br><br>
								Campaigns: <?php echo $user->getCampaignCount(); ?><br><br>
								People you Followed on Twitter: <?php echo $user->getTwitterFollowingCount(); ?>
							</div>
						</div>
						<div class="row-fluid">
							<div class="well text-left">
								<label for="account-type">Subscription Info</label>
								<span class="input-block-level"><?php echo $user->getAllowance(); ?></span>
							</div>
							<?php if ($company === 'opheme' && ci_ === true) { ?>
							<div class="well text-left">
								<label for="account-type">Subscription Management</label>
								<span class="input-block-level">Go to our <a href="http://billing.opheme.com">Billing</a> website to manage your subscription.</span>
							</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php include('html_inc/footer.inc'); ?>
		<!-- End Main -->
	</body>
</html>