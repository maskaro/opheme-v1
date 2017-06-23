#!/usr/bin/php -q
<?php

$cwd = getcwd(); //dir of core.sh
chdir ("../lib/modules/TWITTERGEO");
$module_dir = getcwd(); //dir of current module
chdir ($cwd);
chdir ("../var/jobs");
$jobs_dir = getcwd();
chdir ($cwd);

//http://api.twitter.com/1.1/search/tweets.json?q=%20&count=100&geocode=52.2059235,0.1235962,5mi&since_id=0
//job id

//http://php.net/manual/en/function.parse-url.php - Simon D
function convertUrlQuery($query) { 
	$queryParts = explode('&', $query); 	
	$params = array(); 
	foreach ($queryParts as $param) { 
		$item = explode('=', $param); 
		$params[$item[0]] = $item[1]; 
	}
	return $params; 
} 

//OAUTH config data

//application credentials
define("CONSUMER_KEY", "AkxwLf7NmbdVND4FqmkYsQ");
define("CONSUMER_SECRET", "j1hkPEvjsNtkzLlndst7nqs2jTgPITZOjoZqzBUrJDI");

//Razvan - used my own user credentials here, will later need to be modified to use database credentials for each user
define("OAUTH_TOKEN", "15894713-YL5YA7tUcefN6msoH0seUHKlrXiruDUOH425MHb4t");
define("OAUTH_TOKEN_SECRET", "HCnk7PSPYPaNFHQPiwu3DlBe4961rm4D9iDTG5ZeI4");

//get URL CLI param while applying bit of sanitization (parameter 0 is always the script file name)
$url = $_SERVER["argv"][1];
$url_parse = parse_url($url);
//twitter request params
$params = convertUrlQuery($url_parse["query"]);

/*
//gets from current dir to 3 levels above and inside the var/jobs dir
function getDir() {
	
	$dir = getcwd();
	
	for ($i = 0; $i < 3; $i++) {
		$dir = substr($dir, 0, strrpos($dir, "/"));
	}
	
	$dir .= "/var/jobs";
	
}*/

//get job id
$job_id = $_SERVER["argv"][2];

//job dir
//$dir = getDir() . "/" . $jid . "/";
$current_job_dir = $jobs_dir . "/" . $job_id . "/";

/*** OAUTH PHP code ***/

require_once($module_dir . '/twitteroauth/twitteroauth.php');

//function that initiates a connection and returns it
function getConnectionWithAccessToken($oauth_token, $oauth_token_secret) {
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $oauth_token, $oauth_token_secret);
	return $connection;
}

//initiate connection
$connection = getConnectionWithAccessToken(OAUTH_TOKEN, OAUTH_TOKEN_SECRET);

//need to add verification at some point using GET account/verify_credentials

//get content - multi-layered array
$content = $connection->get("search/tweets", $params);

/*** Parse and Store results ***/

//get cordinates from the geocode parameter
$coords = explode(",", $params["geocode"]);

//validates tweet corrdinates as indicated by job
function validate_coords($geo) {
	
	if (isset($geo["coordinates"])) return true;
	
	return false;
	
	/*
	//job coordinates
	global $coords;
	$mylat = (string) trunc($coords[0], 6); $mylng = (string) trunc($coords[1], 6); //$myrad = $coords[2];
	//tweet coordinates
	$lat = (string) $geo[0]; $lng = (string) $geo[1];
	
	//check if job coordinates are within tweet coordinates
	if (stripos($lat, $mylat) !== false && stripos($lng, $mylng) !== false) return true;
	
	//coordinates do not match
	return false;
	*/
	
}

//truncates a decimal number to the number of places specified
function trunc($number, $places) { $power = pow(10, $places); return floor($number * $power) / $power; }

function store_tweet($tweet) {
	
	global $current_job_dir;
	
	//tweet id
	$id = $tweet["id_str"];
	
	//file name
	$file = $current_job_dir . $id;
	//tweet data
	$data = json_encode($tweet);
	
	//write tweet to file
	file_put_contents($file, $data);
	
}

//http://www.if-not-true-then-false.com/2009/php-tip-convert-stdclass-object-to-multidimensional-array-and-convert-multidimensional-array-to-stdclass-object/
function objectToArray($d) {
	if (is_object($d)) {
		// Gets the properties of the given object
		// with get_object_vars function
		$d = get_object_vars($d);
	}
	if (is_array($d)) {
		/*
		* Return array converted to object
		* Using __FUNCTION__ (Magic constant)
		* for recursive call
		*/
		return array_map(__FUNCTION__, $d);
	}
	else {
		// Return array
		return $d;
	}
}

$content_arr = objectToArray($content);

if (isset($content_arr["statuses"])) {

	//each $value is a tweet
	foreach (@$content_arr["statuses"] as $tweet) {
		
		//if tweet is valid
		if (validate_coords($tweet["geo"]) === true) {
			
			//store it to disk
			store_tweet($tweet);
			
		}
		
	}

}

//store max_id_str to file
if (isset($content_arr["search_metadata"]["max_id_str"])) file_put_contents($current_job_dir . "lastid", $content_arr["search_metadata"]["max_id_str"]);

?>
