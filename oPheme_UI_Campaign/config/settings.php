<?php

//db settings
$db_gen = array(
	'username' => 'opheme',
	'password' => 'oPheme1357!',
	'host' => 'localhost',
	'dbname' => 'opheme'
);

$db_sec_sess = array(
	'username' => 'sec_user',
	'password' => 'sec1357!',
	'host' => 'localhost',
	'dbname' => 'secure_login'
);

//general settings - make sure there are trailing slashes on folder names
$settings = array();

define('logs_path', '/opt/opheme_system_logs/');

function get_domain($url) {
	$domain = strtolower($url);
	//more than 1 dot means there's a subdomain there
	if (substr_count($domain, '.') > 1) $domain = substr($domain, strpos($domain, '.') + 1);
	if (substr_count($domain, '.') > 0) $domain = substr($domain, 0, strpos($domain, '.'));
	return $domain;
}

if (substr_count($_SERVER['SERVER_NAME'], 'ci_') > 0) { $company = 'opheme'; define('ci_', true); define('demo', false); }
elseif (substr_count($_SERVER['SERVER_NAME'], '_') > 0) { $company = strtolower(substr($_SERVER['SERVER_NAME'], 0, stripos($_SERVER['SERVER_NAME'], '_'))); define('demo', true); define('ci_', false);}
else { $company = get_domain($_SERVER['SERVER_NAME']); define('demo', false); }

function cwd_get_root($loc) {
	if (!endsWith($loc, 'opheme')) {
		$loc = substr($loc, 0, strrpos($loc, '/'));
		return cwd_get_root($loc);
	} else return $loc;
}

function endsWith($haystack, $needle) { return $needle === "" || substr($haystack, -strlen($needle)) === $needle; }

$location = cwd_get_root(getcwd());
$company_files = $location . '/Rebrands/' . $company;

if (demo === true) define('domain', 'opheme.com');
else define('domain', file_get_contents($company_files . '/words/domain_name.inc'));
define('brand', file_get_contents($company_files . '/words/brand_name.inc'));
define('company', file_get_contents($company_files . '/words/company_name.inc'));

if ($company == 'opheme') define('enable_emails', true);
else define('enable_emails', false);

define('current_year', date("Y"));

//Twitter
//application credentials
define("CONSUMER_KEY", "ixsSi0R6alETD4hsZjq6YkZA2");
define("CONSUMER_SECRET", "wwCR1Sf1zzsKC0oAbL3J0uT6MAaRScB9vQT6gaMZtSBl8E6x5s");
define('OAUTH_CALLBACK', 'http://' . (@ci_===true?'ci_':'') . (@demo===true?$company . '_c':'campaign') . '.' . domain . '/twitter_callback');

?>