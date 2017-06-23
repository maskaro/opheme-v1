<?php

	require('php/common.php');
	
	if (isset($_SESSION['user']['email'])) {
		header("Location: /account");
        die("Already logged in. Redirecting to Account ...");
	}
	
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo brand; ?> | Login / Register</title>
		<!-- CSS -->
		<?php include("html_inc/css.inc"); ?>
		<!-- JS -->
		<?php include('html_inc/js.inc'); ?>
	</head>
	<body>
		<div id="form" class="container">
			<div class="row">
				<div class="span6 offset3">
					<?php
						if (is_file($company_files . '/logos/large-logo.png'))
							echo '<img src="data:image/png;base64,' . base64_encode(file_get_contents($company_files . '/logos/large-logo.png')) . '" />';
					?>
				</div>
			</div>
			<div class="row">
				<div id="flip-toggle" class="span4 offset4 flip-container">
					<div class="well flipper">
						<div class="front">
							<form id="login" action="/login-process" method="post">
								<fieldset>
									<legend>Login</legend>
									<?php echo (isset($_SESSION['register_ok'])?'<div class="alert alert-success">Registration done! Please check your INBOX and/or SPAM email folders.</div>':''); unset($_SESSION['register_ok']); ?>
									<?php echo (isset($_SESSION['confirm_ok'])?'<div class="alert alert-success">Account confirmed! You may now proceed to login.</div>':''); unset($_SESSION['confirm_ok']); ?>
									<?php echo (isset($_SESSION['login_message'])?'<div class="alert alert-error">' . $_SESSION['login_message'] . '</div>':''); unset($_SESSION['login_message']); ?>
									<?php echo ((isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] > 0)?'<div class="alert">Login attempts: ' . $_SESSION['login_attempts'] . '.</div>':''); unset($_SESSION['login_attempts']); ?>
									<input type="text" class="input-block-level" name="email" placeholder="email" required="required" value="" />
									<input type="password" class="input-block-level" name="password" placeholder="password" required="required" value="" />
									<input type="submit" class="btn btn-large btn-primary btn-block" value="Login" />
								</fieldset>
							</form>
							<form id="forgot" action="/reset-process" method="post">
								<fieldset>
									<legend>I forgot my Password</legend>
									<h5>Enter the email address connected with your <?php echo brand; ?> Account</h5>
									<?php echo (isset($_SESSION['reset_ok'])?'<div class="alert alert-success">Password has been successfully reset! Please check your INBOX and/or SPAM email folders.</div>':''); unset($_SESSION['register_ok']); ?>
									<?php echo (isset($_SESSION['reset_message'])?'<div class="alert alert-error">' . $_SESSION['reset_message'] . '</div>':''); unset($_SESSION['reset_message']); ?>
									<input type="text" class="input-block-level" name="email" placeholder="email" required="required" value="" />
									<input type="submit" class="btn btn-large btn-primary btn-block" value="Reset Password" />
								</fieldset>
							</form>
							<?php /*<div id="register-button" class="center button-footer">
								Sign Up
							</div>*/ ?>
						</div>
						<?php /*<div class="back">
							<form id="register" action="/register-process" method="post">
								<fieldset>
									<legend>Sign Up</legend>
									<?php echo (isset($_SESSION['register_message'])?'<div class="alert alert-error">' . $_SESSION['register_message'] . '</div>':''); ?>
									<input type="text" class="input-block-level" name="token" placeholder="secret token" required="required" value="" />
									<input type="email" class="input-block-level" name="email" placeholder="email" required="required" value="" />
									<input type="password" class="input-block-level" id="password" name="password" placeholder="password" required="required" value="" />
									<input type="password" class="input-block-level" name="confirm-password" placeholder="confirm password" required="required" value="" />
									<label class="checkbox">
										<input id="terms" name="terms" type="checkbox" required="required"> I have read and agreed to <a id="terms-button" href="#terms-content" type="button">Terms &amp; Conditions</a>
									</label>
									<!--<label class="input-block-level">
										<img src="/php/captcha_image.php?rand=<?php echo rand(); ?>" id='captchaimg' style="margin: 0px" />
									</label>
									<input type="text" class="input-block-level" name="captcha_code" placeholder="captcha code seen above" required="required" value="" />
									<label class="input-block-level captcha">
										Can't read the image? click <a href='javascript:refreshCaptcha();'>here</a> to refresh.
									</label>-->
									<input type="submit" class="btn btn-large btn-primary btn-block" value="Sign Up" />
								</fieldset>
							</form>
							<div id="login-button" class="center button-footer">
								Log In
							</div>
						</div>*/ ?>
					</div>
				</div>
			</div>
			<div id="terms-content" style="display: none;">
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
		</div>
		<!-- End Main -->
		<script>
			
			$("#login-button").click(function() {
				$('#flip-toggle').toggleClass('flip');
				$.scrollTo( 0, 400 );
				setTimeout("$('#terms-content').fadeOut();",100);
			});
	  
			$("#register-button").click(function() {
				$('#flip-toggle').toggleClass('flip');
				$.scrollTo( 0, 400 );
			});
			
			<?php echo (isset($_SESSION['register_message'])?'$("#register-button").click();':''); unset($_SESSION['register_message']); ?>
			
			if (window.location.href.indexOf("register") >= 0) $("#register-button").click();
	  
			$("#terms-button").click(function() {
				$("#terms-content").fadeToggle();
				$.scrollTo( '#terms-content', 400 );
			});
	  
			$(".back-up").click(function() {
				$.scrollTo( 0, 400 );
				setTimeout("$('#terms-content').fadeToggle();",200);
			});
			
			$("form#login").validate({
				errorClass: "alert alert-error",
				validClass: "alert alert-success",
				//validation rules
				rules: {
					email: {
						required: true,
						email: true
					},
					password: "required"
				},
				messages: {
					email: "Please enter a valid email address.",
					password: "Please enter your password."
				}
			});
			
			$("form#forgot").validate({
				errorClass: "alert alert-error",
				validClass: "alert alert-success",
				//validation rules
				rules: {
					email: {
						required: true,
						email: true
					}
				},
				messages: {
					email: "Please enter a valid email address."
				}
			});
			
			$("form#register").validate({
				errorClass: "alert alert-error",
				validClass: "alert alert-success",
				//validation rules
				rules: {
					token: "required",
					email: {
						required: true,
						email: true
					},
					password: "required",
					'confirm-password': {
						required: true,
						equalTo: "#password"
					},
					terms: "required",
					//captcha_code: "required"
				},
				messages: {
					token: "Please enter the Secret Token given to you.",
					email: "Please enter a valid email address.",
					password: "Please enter your password.",
					'confirm-password': {
						required: "Please enter your password again.",
						equalTo: "Please enter same password as above."
					},
					terms: "You must agree to our Terms and Conditions.",
					//captcha_code: "Please enter the captcha code as seen in the image below."
				}
			});
			
			function refreshCaptcha() {
				var img = document.images['captchaimg'];
				img.src = img.src.substring(0,img.src.lastIndexOf("?"))+"?rand="+Math.random()*1000;
			}
		</script>
	</body>
</html>