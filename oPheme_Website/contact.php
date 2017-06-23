<?php session_start(); ?>
<!DOCTYPE html>
<html>
	<head>
		<title>oPheme | Get in Touch</title>
		<?php include("html_inc/css.inc"); ?>
		<?php include("html_inc/js.inc"); ?>
	</head>
	<body>
		<!-- Sticky Navigation -->
		<?php include("html_inc/nav.inc"); ?>
		<!-- End Sticky Navigation -->
		<!-- Start Main -->
		<div class="productSpace productBackground">
			<div class="container">
				<img src="img/oPheme-logo.png" alt="logo" />
				<p>Social Media Monitoring Toolkit</p>
				<div class="description marketing">
					<hr class="soften" />
					<div class="description row-fluid">
						<div class="span12">
							<h1>Get in touch!</h1>
						</div>
					</div>
					<div class="description row-fluid">
						<div class="span12">
							<img class="marketing-img" src="img/phem.png" />
							<p>If you would like to know more either about us or about our products or would like to start a free 30 day Trial, please contact us using the form below, we'd love to hear from you!</p>
						</div>
						<div class="span12">
							<form id="form" action="/contact-process" method="post">
								<fieldset>
									<?php echo (isset($_SESSION['contact_ok'])?'<div class="alert alert-success">Thank you for getting in touch! We will get back to you ASAP. Make sure you check you Email SPAM folder for an @opheme.com message</div>':''); unset($_SESSION['contact_ok']); ?>
									<?php echo (isset($_SESSION['contact_message'])?'<div class="alert alert-error">' . $_SESSION['contact_message'] . '</div>':''); unset($_SESSION['contact_message']); ?>
									<input type="text" class="input-block-level" name="name" placeholder="your full name" required="required" value="" />
									<input type="text" class="input-block-level" name="company" placeholder="your company name" required="required" value="" />
									<input type="text" class="input-block-level" name="email" placeholder="email" required="required" value="" />
									<textarea class="input-block-level" name="message" placeholder="your message" required="required"></textarea>
									<input type="submit" class="btn btn-large btn-primary btn-block" value="Send!" />
								</fieldset>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php include('html_inc/footer.inc'); ?>
		<script>
			$("form#form").validate({
				errorClass: "alert alert-error",
				validClass: "alert alert-success",
				//validation rules
				rules: {
					name: "required",
					company: "required",
					email: {
						required: true,
						email: true
					},
					message: "required"
				},
				messages: {
					name: "Please enter your full name.",
					company: "Please enter your company name.",
					email: "Please enter a valid email address.",
					message: "Please enter your message."
				}
			});
		</script>
		<!-- End Main -->
	</body>
</html>