<!DOCTYPE html>
<html>
	<head>
		<title>oPheme | Terms of Service</title>
		<?php include("html_inc/css.inc"); ?>
		<?php include("html_inc/js.inc"); ?>
		<style>
			a:hover {
				text-decoration: none;
			}
		</style>
	</head>
	<body>
		<!-- Sticky Navigation -->
		<?php include("html_inc/nav.inc"); ?>
		<!-- End Sticky Navigation -->
		<div class="productSpace productBackground">
			<div class="container">
				<div class="row-fluid">
					<div class="span12" id="terms-content">
						<?php include('html_inc/tac.inc'); ?>
					</div>
				</div>
				<div class="back-up button-footer">
					<i class="icon-angle-up icon-2x"></i><br />
					Back to Top
				</div>
				<br /><br /><br /><br />
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