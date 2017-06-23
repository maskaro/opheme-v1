<?php

//top level class
abstract class oPhemeUI {
	
	//plugin general settings
	protected $settings;
	
	//specific info
	protected $job_info;
	
	//initial constructor
	public function __construct($info) {
		$this->settings = isset($info['settings'])?$info['settings']:array();
		$this->job_info = isset($info['job_info'])?$info['job_info']:array();
	}
	
}

//oPheme default operations
abstract class oPhemeUI_Ops extends oPhemeUI {
	
	//must create separate classes for each service e.g.: Twitter
	abstract public function createJob();
	abstract public function getJobSpecs();
	abstract public function checkJob();
	abstract public function getJobListings();
	abstract protected function validateJob();
	abstract protected function createJobDirStructure($id);
	abstract protected function parseJobSpecs($string);
	abstract protected function parseMessage($string);
	abstract protected function getNewJobMessages($job_id, $refresh);
	
	//http://roshanbh.com.np/2007/12/getting-real-ip-address-in-php.html
	protected function getUserIP() {

		if (!empty($_SERVER['HTTP_CLIENT_IP'])) { //check ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { //to check ip is pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		
		return $ip;
		
	}
	
	//log errors/warnings/notices to file
	protected function log($type, $msg) {
		
		//vars
		$result = false;
		$toFile = $this->settings['log_files_directory'] . $this->settings['log_files'][$type];
		$time = date('l jS \of F Y h:i:s A');
		
		//add timestamp
		$msg = $time . ' - ' . $msg;
		
		//attempt to write to log file
		if (!$this->writeTo($toFile, $msg, false)) {
			
			//trigger PHP error if this fails
			trigger_error('Cannot write to log file of type "' . $type . '" in file "' . $toFile . '".', E_USER_ERROR);
			
		}
		
	}
	
	//write/append to file
	//if $toLog is false, the log function will not be called - prevents recursive infinite loops
	protected function writeTo($file, $contents = '', $toLog = true, $append = true) {
		
		//was it opened successfully?
		
		if ($append) $handle = fopen($file, 'ab');
		else $handle = fopen($file, 'wb');
		
		if ($handle = fopen($file, 'ab')) {
			
			//was it written to successfully?
			if (fwrite($handle, $contents) >= 0) { // if file is simply being created, value of 0 bytes will be returned, but still successful
				
				//log action
				if ($toLog) $this->log('notice', 'Successfully wrote to file "' . $file . '".' . "\n");
				
				//close file
				fclose($handle);
				
				//succeeded
				return true;
				
			} else { if ($toLog) $this->log('error', 'Cannot write to file "' . $file . '".' . "\n"); else trigger_error('Cannot write to file "' . $file . '".', E_USER_ERROR); }
			
		} else { if ($toLog) $this->log('error', 'Cannot open file "' . $file . '" to start writing.' . "\n"); else trigger_error('Cannot open file "' . $file . '" to start writing.', E_USER_ERROR); }
		
		//failed
		return false;
		
	}
	
	//returns file contents as array
	protected function readFrom($file) {
		
		return @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		
	}
	
	//filter message for garbage/malicious characters
	protected function filter($msg) {
		
		//initiate filter object
		$filter = new Filters($msg);
		
		//get result
		$result = $filter->results();
		
		//unset object
		unset($filter);
		
		//return result
		return $result;
		
	}
	
}

//Twitter PHP module
class oPhemeUI_Twitter extends oPhemeUI_Ops {
	
	//attempts to validate job specs, returns true or false
	protected function validateJob() {
		
		//initially valid
		$valid = true;
		//job form specs
		$job = $this->job_info['data'];
		
		//make sure source is valid
		switch (strtolower($job['source'])) {
			case 'twitterapi':
				break;
			case 'twittergeo':
				break;
			default:
				$valid = false;
				break;
		}
		
		//latitude and longitude have to be numeric
		if (!is_numeric($job['centre_lat']) || !is_numeric($job['centre_lng'])) $valid = false;
		
		//last 2 should be 'mi', discard that
		if (!is_numeric(substr($job['radius'], 0, -2))) $valid = false;
		
		//if there are any filters specified
		if (strlen($job['filter']) > 0) if (strlen($job['filter']) < $this->settings['job_filter_word_minimum_length']) $valid = false;
		
		//valid email
		if (!filter_var($job['initiating_user_email'], FILTER_VALIDATE_EMAIL)) $valid = false;
		
		//check map
		switch (strtolower($job['map_type'])) {
			case 'gmaps':
				break;
			default:
				$valid = false;
				break;
		}
		
		//true or false
		return $valid;
		
	}
	
	//generate file with contents
	//ad34d..a3134aw!TWITTERAPI!52.205411!0.119047!1mi!marketing@cuba.com!gmaps
	//JOB_ID!SOURCE!COORDS_LAT!COORDS_LNG!DISTANCE!INITIATING_USER_EMAIL!MAP_TYPE
	public function createJob() {
		
		//is job valid?
		if ($this->validateJob()) {
		
			//compile job specs
			$job_specs =
				strtoupper($this->job_info['data']['source']) . '!' .
				$this->job_info['data']['centre_lat'] . '!' .
				$this->job_info['data']['centre_lng'] . '!' .
				$this->job_info['data']['radius'] . '!' .
				$this->job_info['data']['filter'] . '!' .
				$this->job_info['data']['initiating_user_email'] . '!' .
				$this->job_info['data']['map_type'];
				
			//filter string for impurities
			$job_specs = $this->filter($job_specs);
			
			//generate job id
			$ip = str_replace('.', '', $this->getUserIP()); //get user IP and strip dots to get a number
			$job_id = sha1($job_specs . $ip);
			
			//stop duplicate jobs from being created
			if ($this->job_exists($job_id)) return 0;
			
			//append job id to job specs
			$job_specs = $job_id . '!' . $job_specs;
			
			//write job to file
			$this->writeTo($this->settings['jobs_file_directory'] . $this->settings['jobs_file_name'], $job_specs . "\n");
			
			//create job directory structure
			$this->createJobDirStructure($job_id);
			
			//compile job specs array
			$job_specs_arr = array(
				'source' => strtoupper($this->job_info['data']['source']),
				'map_type' => $this->job_info['data']['map_type'],
				'centre_lat' => $this->job_info['data']['centre_lat'],
				'centre_lng' => $this->job_info['data']['centre_lng'],
				'radius' => $this->job_info['data']['radius'],
				'filter' => $this->job_info['data']['filter'],
				'initiating_user_email' => $this->job_info['data']['initiating_user_email'],
				'id' => $job_id
			);
			
			//return job specs array
			return $job_specs_arr;
		
		}
		
		return 0;
		
	}
	
	//check if exact job exists
	//parameter only used for getting info on multiple jobs on system
	private function job_exists($id = null) {
		
		$job_id = ($id?$id:$this->job_info['id']);
		
		$job_dir = $this->settings['jobs_directory'];
		
		//does job dir exist?
		if (is_dir($job_dir)) {
			
			//get location
			$jobs_file_location = $this->settings['jobs_file_directory'] . $this->settings['jobs_file_name'];
			
			//get contents as array
			$jobs_file = $this->readFrom($jobs_file_location);
			
			//parse array
			foreach($jobs_file as $value) {
				
				//get job as array
				$current_job = $this->parseJobSpecs($value);
				
				//if current job id matches the searched id, return the job
				if ($current_job['id'] == $job_id) return true;
				
			}
			
		}
		
		//false if not found - invalid id
		return false;
		
	}
	
	//return full job specs
	//parameter only used for getting info on multiple jobs on system
	public function getJobSpecs($id = null) {
		
		$job_id = ($id?$id:$this->job_info['id']);
		
		$job_dir = $this->settings['jobs_directory'];
		
		//does job dir exist?
		if (is_dir($job_dir)) {
			
			//get location
			$jobs_file_location = $this->settings['jobs_file_directory'] . $this->settings['jobs_file_name'];
			
			//get contents as array
			$jobs_file = $this->readFrom($jobs_file_location);
			
			//parse array
			foreach($jobs_file as $value) {
				
				//get job as array
				$current_job = $this->parseJobSpecs($value);
				
				//if current job id matches the searched id, return the job
				if ($current_job['id'] == $job_id) return $current_job;
				
			}
			
		}
		
		//false if not found - invalid id
		return false;
		
	}
	
	//get all jobs on system
	public function getJobListings() {
		
		//jobs directory location
		$jobs_dir = $this->settings['jobs_directory'];
		$jobs_file = $jobs_dir . $this->settings['jobs_file_name'];
		
		//get all current job messages
		//$jobs_array = scandir($jobs_dir);
		$jobs_array = $this->readFrom($jobs_file);
		
		//get total job count
		//$count = count($jobs_array) - 4; //exclude ., .., jobs.txt and agent.channels
		$count = count($jobs_array);
		
		//any jobs
		if ($count > 0) {
			
			//define return vars
			$job_ids = array();
			
			//parse files
			//foreach($jobs_array as $job_id) {
			foreach ($jobs_array as $value) {
				
				//disabled job
				if (substr($value, 0, 1) == "#") continue;
				
				//ignore irrelevant folders
				//if (stristr($job_id, '.') || stristr($job_id, '..') || stristr($job_id, $this->settings['jobs_file_name']) || stristr($job_id, 'agent.channels')) continue;
				
				//get job spec
				$job_spec = $this->parseJobSpecs($value);
				
				//build new message array
				//array_push($job_ids, $this->getJobSpecs($job_id));
				array_push($job_ids, $job_spec);
				
			}
			
			return $job_ids;
			
		}
		
		//no messages at all or refresh not requested
		return 0;
		
	}
	
	//check a job id and return latest messages array
	public function checkJob() {
		
		//get id
		$job_id = isset($this->job_info['id'])?$this->job_info['id']:$this->job_info['info']['id'];
		
		//return result
		return $this->getNewJobMessages($job_id, intval($this->job_info['info']['refresh']));
		
	}
	
	//create dir structure
	protected function createJobDirStructure($id) {
		
		//jobs directory
		$dir = $this->settings['jobs_directory'];
		$res_file = $this->settings['job_results_file_name'];
		$last_id_file = $this->settings['job_files']['last_id'];
		$latest_id_file = $this->settings['job_files']['latest_id_not_relevant'];
		
		$create = $dir . $id;
		
		//create the dir
		if (mkdir($create, 0777)) chmod($create, octdec(777)); //make sure it is in fact 777
		else throw new Exception('Could not create <' . $create . '>. Check folder permissions.'); //make PHP die if folder could not be created...
		
		//create last id file
		$this->writeTo($create . '/' . $last_id_file, '0');
		
		//create latest id file
		$this->writeTo($create . '/' . $latest_id_file, '0');
		
	}
	
	//from string to associative array
	protected function parseJobSpecs($string) {
		
		//explode string to array
		$array = explode('!', $string);
		
		//compile associative array
		$assoc_array = array(
			'id' => $array[0],
			'source' => $array[1],
			'centre_lat' => $array[2],
			'centre_lng' => $array[3],
			'radius' => $array[4],
			'filter' => $array[5],
			'initiating_user_email' => $array[6],
			'map_type' => $array[7]
		);
		
		//return assoc array
		return $assoc_array;
		
	}
	
	//parse message JSON string to array
	/*
		"created_at":"Thu, 06 Dec 2012 21:33:17 +0000",
		"from_user":"_leeaaa",
		"from_user_id":45941536,
		"from_user_id_str":"45941536",
		"from_user_name":"fotheringham.",
		"geo":null,
		"location":"city ground",
		"id":276801509121982464,
		"id_str":"276801509121982464",
		"iso_language_code":"en",
		"metadata":{
		   "result_type":"recent"
		},
		"profile_image_url":"http://a0.twimg.com/profile_images/2665970897/42d5fe20c0c291913b5a79eb7c3d51d3_normal.jpeg",
		"profile_image_url_https":"https://si0.twimg.com/profile_images/2665970897/42d5fe20c0c291913b5a79eb7c3d51d3_normal.jpeg",
		"source":"&lt;a href=&quot;http://twitter.com/download/iphone&quot;&gt;Twitter for iPhone&lt;/a&gt;",
		"text":"@JoeLeShark dont flatter yourself",
		"to_user":"JoeLeShark",
		"to_user_id":65089111,
		"to_user_id_str":"65089111",
		"to_user_name":"Joe Sharphouse",
		"in_reply_to_status_id":276801110478569473,
		"in_reply_to_status_id_str":"276801110478569473"
	*/
	protected function parseMessage($string) {
		
		//explode string to array
		$array = json_decode($string);
		
		//return assoc array
		return $array;
		
	}
	
	//check for new job messages - scans job directory, one message per file
	protected function getNewJobMessages($job_id, $refresh = 0) {
		
		//job directory location
		$job_dir = $this->settings['jobs_directory'] . $job_id . '/';
		
		//get all current job messages
		$messages_array = scandir($job_dir);
		
		//get total message count
		$count = count($messages_array) - 4; //exclude ., .., lastid.real and lastid
		
		//any messages
		if ($count) {
			
			//last id
			$last_id_file = $job_dir . $this->settings['job_files']['last_id'];
			$last_id = implode($this->readFrom($last_id_file));
			
			//latest id
			$latest_id = $messages_array[count($messages_array) - 4]; //deduct 4 because of first . .. lastid.real and lastid files
			
			//define return vars
			$job_messages_old_return = array();
			$job_messages_new_return = array();
			
			//parse files
			foreach($messages_array as $message_id) {
				
				//irrelevant files
				if (stristr($message_id, '.') || stristr($message_id, '..') || stristr($message_id, $this->settings['job_files']['latest_id_not_relevant']) || stristr($message_id, $this->settings['job_files']['last_id'])) continue;
				
				//get message contents - string
				$message_contents = $this->readFrom($job_dir . $message_id);
				
				//filter the message for impurities
				$message_contents = $this->filter(implode($message_contents));
				
				//transform to object
				$message_as_object = $this->parseMessage($message_contents);
				
				//is message required?
				if ($this->returnMessage($message_as_object->text)) {
				
					//new message
					if ($message_id > $last_id) {
						
						//build new message array
						array_push($job_messages_new_return, $message_as_object);
					
					//old message, add to array only if requested
					} elseif ($refresh == 1) {
						
						//build old message array
						array_push($job_messages_old_return, $message_as_object);
						
					}
					
				}
				
			}
			
			//refresh required, doesn't matter what we found so far
			if ($refresh == 1) {
				
				//return all, including older
				return array_merge($job_messages_old_return, $job_messages_new_return);
			
			//no refresh, only new messages if any
			} else {
				
				//there were new messages
				if ($latest_id > $last_id) {
					
					//set last id to be latest id
					$this->writeTo($last_id_file, $latest_id, true, false);
					
					//return new messages
					return $job_messages_new_return;
				
				}
				
			}
			
		}
		
		//no messages at all or refresh not requested
		return 0;
		
	}
	
	//if message string contains
	protected function returnMessage($message) {
		
		//words filter string, words separated by space
		$words = $this->job_info['info']['filter'];
		$min_len = $this->settings['job_filter_word_minimum_length'];
		
		//total length check, will check all words later
		if (strlen($words) >= $min_len) {
			
			//words filter array
			$words = explode(' ', $words);
			
			//look at all words
			foreach ($words as $word) {
				
				//if current filter word is less than minimum required, then skip it
				if (strlen($word) < $min_len) continue;
				
				//if message matches any word, then message has to be displayed, stop looking
				if (stristr($message, $word)) return true;
				
			}
			
			//no matched words
			return false;
			
		}
		
		//not enough word length to filter by, so don't filter
		return true;
		
	}
	
}

//filter string/array of strings class
//needs proper filtering code
//meant to be a sanitization class, removes bad data
class Filters {
	
	//input to filter
	protected $toFilter;
	//results
	protected $result;
	
	public function __construct($msg) {
		
		$this->toFilter = $msg;
		
		if (is_array($this->toFilter)) { //array of strings
			$this->result = array();
			$this->filter_array();
		}
		else { //simple string
			$this->result = $this->filter_string($this->toFilter);
		}
		
	}
	
	//parse array of strings and filter
	private function filter_array() {
		
		foreach($this->toFilter as $value) {
			array_push($this->result, $this->filter_string($value));
		}
		
	}
	
	//filter string
	private function filter_string($string) {
		
		return $string;
		
	}
	
	//return result
	public function results() {
		
		return $this->result;
		
	}
	
}

?>
