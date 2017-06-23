<?php

	require_once("php/common.php");
	require_once("php/protect.php");

?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo brand; ?> | Terms of Service</title>
		<!-- CSS -->
		<?php include("html_inc/css.inc"); ?>
		<!-- JS -->
		<?php include('html_inc/js.inc'); ?>
		<?php include('html_inc/campaign_editor_scripts.inc'); ?>
		<?php include('html_inc/campaigns_scripts.inc'); ?>
	</head>
	<body>
		<!-- Sticky Navigation -->
		<?php include("html_inc/navigation.inc"); ?>
		<!-- End Sticky Navigation -->
		<div id="terms-content" style="margin-top: 60px">
			<div class="row-fluid">
				<div class="span12">
					<?php if (is_file($company_files . '/terms/tac.inc')) include($company_files . '/terms/tac.inc'); ?>
				</div>
			</div>
			<div class="back-up button-footer">
				<i class="icon-angle-up icon-2x"></i><br />
				Back to Top
			</div>
		</div>
		<?php include('html_inc/footer.inc'); ?>
		<script type="text/javascript">
			$(".back-up").click(function() {
				$.scrollTo( 0, 400 );
			});
		</script>
	</body>
</html>