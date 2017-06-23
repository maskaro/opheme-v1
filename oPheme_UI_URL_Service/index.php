<?php

	require_once("php/common.php");
	
	if (isset($_GET['web_id'])) {
	
		$opheme->campaign_setData($_GET);
		$data = $opheme->campaign_getSpecs();
		
		if ($data != false) {
			
			$_SESSION['web_id'] = $data['web_id'];
			
			?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo file_get_contents($company_files . '/words/brand_name.inc'); ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<!-- Bootstrap -->
		<link href="/css/bootstrap.css" rel="stylesheet" media="screen" />
		<link href="/css/responsive.css" rel="stylesheet" media="screen" />
		<link href="/css/main.css" rel="stylesheet" media="screen" />
		<?php
			if (is_file($company_files . '/css/changes.css'))
				echo '<style media="screen">' . file_get_contents($company_files . '/css/changes.css') . '</style>';
			if (is_file($company_files . '/favicon/favicon.png'))
				echo '<link href="data:image/x-icon;base64,' . base64_encode(file_get_contents($company_files . '/favicon/favicon.png')) . '" rel="icon" type="image/x-icon" />';
		?>
	</head>
	<body>
		<!-- Sticky Navigation -->
		<div class="navbar navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container">
					<!-- Be sure to leave the brand out there if you want it shown -->
					<?php
						if (is_file($company_files . '/logos/small-logo.png'))
							echo '<a class="brand" href="/account"><img src="data:image/png;base64,' . base64_encode(file_get_contents($company_files . '/logos/small-logo.png')) . '" /></a>';
					?>
				</div>
			</div>
		</div>
		<!-- End Sticky Navigation -->
		<div id="dashboard" class="container campaign">
			<div class="row-fluid">
				<div class="span6 offset3">
					<div id="campaign-container">
						<h4>Hey there, <strong>@<?php echo $data['tweet']['user']['screen_name']; ?></strong>!<br />You have received a message from <strong><a href="<?php echo $data['business']['www']; ?>" target="_blank"><?php echo $data['business']['name']; ?></a></strong>.</h4>
						<div class="row-fluid">
							<div class="span12">
								<label for="tweet-text">Your tweet that triggered this message</label>
								<span class="input-block-level"><?php echo $data['tweet']['text']; ?></span>
							</div>
						</div>
						<div class="row-fluid">
							<div class="span12">
								<label for="campaign-text">Message from <strong><a href="<?php echo $data['business']['www']; ?>" target="_blank"><?php echo $data['business']['name']; ?></a></strong></label>
								<span class="input-block-level"><?php echo $data['campaign']['text']; ?></span>
							</div>
						</div>
						<?php if (strlen($data['campaign']['banner']) > 0) { ?>
						<div class="row-fluid">
							<div class="span12">
								<label for="campaign-banner">Campaign Banner</label>
								<span class="input-block-level"><img src='data:<?php echo $data['campaign']['banner_type']; ?>;base64,<?php echo $data['campaign']['banner']; ?>' title='Banner' /></span>
							</div>
						</div>
						<?php } ?>
						<div class="row-fluid">
							<div class="span12">
								<label for="tweet-created-at">Date of tweet</label>
								<span class="input-block-level"><?php echo $data['tweet']['created_at']; ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<br />
			<div class="row-fluid">
				<div class="span6 offset3">
					<div id="blacklist-container">
						<h4><?php echo file_get_contents($company_files . '/words/brand_name.inc'); ?> Marketing Preferences</h4>
						<?php echo (isset($_SESSION['twitter_ok'])?'<div class="alert alert-success">' . $_SESSION['twitter_ok'] . '</div>':''); unset($_SESSION['twitter_ok']); ?>
						<?php echo (isset($_SESSION['twitter_message'])?'<div class="alert alert-error">' . $_SESSION['twitter_message'] . '</div>':''); unset($_SESSION['twitter_message']); ?>
						<form id="preferences" action="/preferences-process" method="post">
							<div class="row-fluid">
								<div class="span12 form-inline">
									<label>Send me offers related to</label>
									<div class="controls">
										<?php
											$cats = array("Bars", "Clubs", "Restaurants", "Clothing", "Music", "General Shopping");
											foreach($cats as $cat)
												echo '<label class="checkbox" for="' . $cat . '" style="padding-top: 0px">
														<input type="checkbox" name="preferences[]" value="' . $cat . '"' . ((in_array($cat, $data['user_prefs']))?' checked="checked"':'') . ' /> ' . $cat . ' 
													</label> ' . PHP_EOL;
										?>
									</div>
								</div>
							</div>
							<div class="row-fluid">
								<div class="span12">
									<input type="password" class="input-block-level" id="password" name="password" placeholder="type your password to save preferences, 8 characters or more" required="required" value="" />
									<?php if (count($data['user_prefs']) == 0) { ?><input type="password" class="input-block-level" name="confirm-password" placeholder="confirm password, same as above" required="required" value="" /><?php } ?>
								</div>
							</div>
							<input type="hidden" id="screen_name" name="screen_name" value="<?php echo $data['tweet']['user']['screen_name']; ?>" />
							<div class="row-fluid">
								<div class="span12">
									<input class="btn btn-large btn-block btn-primary" type="submit" value="Save Preferences" />
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
			<br />
			<div class="row-fluid">
				<div class="span6 offset3">
					<div id="blacklist-container">
						<?php /*<h4>Blacklist Management</h4>*/ ?>
						<?php echo (isset($_SESSION['twitter_blacklist_ok'])?'<div class="alert alert-success">' . $_SESSION['twitter_blacklist_ok'] . '</div>':''); unset($_SESSION['twitter_blacklist_ok']); ?>
						<?php echo (isset($_SESSION['twitter_blacklist_message'])?'<div class="alert alert-error">' . $_SESSION['twitter_blacklist_message'] . '</div>':''); unset($_SESSION['twitter_blacklist_message']); ?>
						<form id="blacklist" action="/blacklist-process" method="post">
							<?php if (count($data['user_prefs']) > 0) { ?><div class="row-fluid"><div class="span12"><input type="password" class="input-block-level" name="password" placeholder="type your password to blacklist yourself, 8 characters or more" required="required" value="" /></div></div><?php } ?>
							<input type="hidden" id="screen_name" name="screen_name" value="<?php echo $data['tweet']['user']['screen_name']; ?>" />
							<div class="row-fluid">
								<div class="span12">
									<?php if ($data['blacklisted'] == false) { ?><input class="btn btn-large btn-block btn-primary" type="submit" value="I dont want to receive offers!" /><?php } ?>
									<?php if ($data['blacklisted'] == true) { ?><input class="btn btn-large btn-block btn-primary" type="submit" value="I want to receive offers again!" /><?php } ?>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<footer class="footer">
			<div class="container">
				<p>Designed and built with all the love in the world.</p>
			</div>
		</footer>
	</body>
</html>
			<?php
		}
		
		else {
			
			header('Location: http://www.' . file_get_contents($company_files . '/words/domain_name.inc'));
			die("Sorry, to access this service you must use the URL sent to you by " . file_get_contents($company_files . '/words/brand_name.inc') . ", if any.");
			
		}
		
	} else {
		
		header('Location: http://www.' . file_get_contents($company_files . '/words/domain_name.inc'));
		die("Sorry, to access this service you must use the URL sent to you by " . file_get_contents($company_files . '/words/brand_name.inc') . ", if any.");
		
	}

?>