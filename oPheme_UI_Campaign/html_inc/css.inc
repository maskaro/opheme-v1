		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Bootstrap -->
		<link href="/css/bootstrap.css" rel="stylesheet" media="screen">
		<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
		<link href="/css/responsive.css" rel="stylesheet" media="screen">
		<link href="/css/datepicker.css" rel="stylesheet" media="screen">
		<link href="/css/jquery-ui.css" rel="stylesheet" media="screen">
		<link href="/css/jquery.ui.timepicker.css" rel="stylesheet" media="screen">
		<link href="/css/map_cc.css" rel="stylesheet" media="screen">
		<link href="/css/main.css" rel="stylesheet" media="screen">
		<?php
			if (is_file($company_files . '/css/changes.css'))
				echo '<style media="screen">' . file_get_contents($company_files . '/css/changes.css') . '</style>';
			if (is_file($company_files . '/favicon/favicon.png'))
				echo '<link href="data:image/x-icon;base64,' . base64_encode(file_get_contents($company_files . '/favicon/favicon.png')) . '" rel="icon" type="image/x-icon">';
		?>