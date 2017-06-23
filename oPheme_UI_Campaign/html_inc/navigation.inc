		<div class="navbar navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container">
					<!-- .btn-navbar is used as the toggle for collapsed navbar content -->
					<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</a>
					<!-- Be sure to leave the brand out there if you want it shown -->
					<?php
						if (is_file($company_files . '/logos/small-logo.png'))
							echo '<a class="brand" href="/dashboard"><img src="data:image/png;base64,' . base64_encode(file_get_contents($company_files . '/logos/small-logo.png')) . '" /></a>';
					?>
					<!-- Everything you want hidden at 940px or less, place within here -->
					<div class="nav-collapse collapse">
						<ul class="nav">
							<li class="divider-vertical"></li>
							<li class="dropdown<?php if (stripos($_SERVER['PHP_SELF'], 'account') !== false || stripos($_SERVER['PHP_SELF'], 'dashboard') !== false) echo ' active'; ?>">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-tasks icon-large"></i> Account <b class="caret"></b></a>
								<ul class="dropdown-menu">
									<li<?php if (stripos($_SERVER['PHP_SELF'], 'dashboard') !== false) echo ' class="active"'; ?>><a href="/dashboard"><i class="icon-home icon-large"></i> Dashboard</a></li>
									<li<?php if (stripos($_SERVER['PHP_SELF'], 'account') !== false) echo ' class="active"'; ?>><a href="/account"><i class="icon-user icon-large"></i> Details</a></li>
									<li><a href="/logout"><i class="icon-signout icon-large"></i> Log out</a></li>
								</ul>
							</li>
							<?php if ($_SESSION['showCreationModule']) { ?>
							<li class="divider-vertical"></li>
							<li<?php if (stripos($_SERVER['PHP_SELF'], 'campaigns') !== false) echo ' class="active"'; ?>><a href="/campaigns"><i class="icon-bar-chart icon-large"></i> Campaigns</a></li>
							<li class="divider-vertical"></li>
							<?php if (stripos($_SERVER['PHP_SELF'], 'campaigns') !== false) { ?>
							<li id="new-campaign-nav"><a href="#"><i class="icon-plus icon-large"></i> New Campaign</a></li>
							<li class="divider-vertical"></li>
							<?php } ?>
							<li><a href="http://<?php echo (@ci_===true?'ci_':''); ?><?php echo (@demo===true?$company . '_d':'discover'); ?>.<?php echo domain; ?>/discovers<?php /*echo '?session_id=' . session_id();*/ ?>"><i class="icon-bar-chart icon-large"></i> Discovers</a></li>
							<?php } ?>
							<?php if (intval($_SESSION['user']['reseller']) === 1) { ?>
							<li class="divider-vertical"></li>
							<li<?php if (stripos($_SERVER['PHP_SELF'], 'reseller') !== false) echo ' class="active"'; ?>><a href="/reseller"><i class="icon-fire icon-large"></i> Users</a></li>
							<?php } ?>
							<?php if (intval($_SESSION['user']['admin']) === 1 && $company == 'opheme') { ?>
							<li class="divider-vertical"></li>
							<li<?php if (stripos($_SERVER['PHP_SELF'], 'admin') !== false) echo ' class="active"'; ?>><a href="/admin"><i class="icon-fire icon-large"></i> Admin</a></li>
							<?php } ?>
							<?php if ($company == 'opheme') echo '<li class="divider-vertical"></li><li><a href="http://support.opheme.com" target="_blank"><i class="icon-wrench icon-large"></i> Support</a></li>'; ?>
						</ul>
						<!-- .nav, .navbar-search, .navbar-form, etc -->
					</div>
				</div>
			</div>
		</div>