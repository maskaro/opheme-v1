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
					<a class="brand" href="/index">o<span class="orange">:P</span>heme</a>
					<!-- Everything you want hidden at 940px or less, place within here -->
					<div class="nav-collapse collapse">
						<ul class="nav pull-right">
							<li<?php if (stripos($_SERVER['PHP_SELF'], 'index') !== false) echo ' class="active"'; ?>><a href="/index">All About oPheme</a></li>
							<li class="divider-vertical"></li>
							<li<?php if (stripos($_SERVER['PHP_SELF'], 'contact') !== false) echo ' class="active"'; ?>><a href="/contact">Get in Touch</a></li>
							<li class="divider-vertical"></li>
							<li<?php if (stripos($_SERVER['PHP_SELF'], 'support') !== false) echo ' class="active"'; ?>><a href="/support">Support</a></li>
							<li class="divider-vertical"></li>
							<li<?php if (stripos($_SERVER['PHP_SELF'], 'terms') !== false) echo ' class="active"'; ?>><a href="/terms">Terms of Service</a></li>
							<li class="divider-vertical"></li>
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">Login Into <b class="caret"></b></a>
								<ul class="dropdown-menu">
									<li><a href="http://discover.opheme.com/login">oPheme Discover</a></li>
									<li><a href="http://campaign.opheme.com/login">oPheme Campaign</a></li>
								</ul>
							</li>
							<!--<li><a href="/about">About</a></li>-->
							<!--<li class="divider-vertical"></li>-->
							<!--<li><a href="/faq">FAQ</a></li>-->
							<!--<li class="divider-vertical"></li>-->
							<!--<li><a href="/help">Help</a></li>-->
						</ul>
						<!-- .nav, .navbar-search, .navbar-form, etc -->
					</div>
				</div>
			</div>
		</div>