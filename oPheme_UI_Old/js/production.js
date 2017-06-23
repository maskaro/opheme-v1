//for (var prop in job_id) { alert(prop + " = " + job_id[prop]); }

//TODO: shorten URL service

var oph,
	job_id,
	map;

$(function(){
	
	//initialise plugin
	oph = new $.oPhemeUI($("#map_canvas"), {
		timeout: 5000 //run every 5 sec
	});
	
	//check path for job id
	
	//get path and split it
	var a = location.search.split("?");
	
	//check for job id - assume it's the last thing there, so far anyway
	job_id = a[a.length - 1];
	
	//job id - string and appropriate length?
	if (typeof job_id === 'string' && job_id.length == 40) { //runs when job id is given
		
		//$('#landingModal .hero-unit').html("<h1>O:Pheme</h1><p>View tweets in your neighbourhood...wait what!?</p><p>Loading... Waiting for messages to be matched and processed... <br /><img src='img/loader.gif'</p>");
		
		//in order to re-use index.html, form and map must be cleared
		oph.clearMapElement();
		
		//get job specs
		var job_info = oph.getJobSpecs(job_id);
		
		//populate form fields
		$('form#job_form input[name=source]').val(job_info.source);
		$('form#job_form input[name=map_type]').val(job_info.map_type);
		$('form#job_form input[name=centre_lat]').val(job_info.centre_lat);
		$('form#job_form input[name=centre_lng]').val(job_info.centre_lng);
		$('form#job_form input[name=radius]').val(job_info.radius.slice(0, -2));
		$('form#job_form input[name=filter]').val(job_info.filter);
		$('form#job_form input[name=initiating_user_email]').val(job_info.initiating_user_email);
		
		//compute radius
		var radius = parseInt(job_info.radius.slice(0, -2)),
			map_zoom;

		switch(true) {
			case ((radius > 0) && (radius <= 1)):
				map_zoom = 14;
				break;
			case ((radius > 1) && (radius <= 3)):
				map_zoom = 13;
				break;
			case ((radius > 3) && (radius <= 7)):
				map_zoom = 12;
				break;
			case ((radius > 7) && (radius <= 10)):
				map_zoom = 11;
				break;
			case ((radius > 10) && (radius <= 20)):
				map_zoom = 10;
				break;
			case ((radius > 20) && (radius <= 50)):
				map_zoom = 9;
				break;
			default:
				map_zoom = 8;
				break;
		}
		
		//set up job map
		map = oph.map({
			api: job_info.map_type,
			settings: {
				options: {
					map_centre: {
						lat: job_info.centre_lat,
						lng: job_info.centre_lng
					},
					zoom: map_zoom
				},
				noOfMarkers: 50
			}
		});
		
		//start job - get messages, store them in queue
		oph.startJob();
		
		//start job queue
		oph.startJobQueue();
		
		//get all jobs on system - TEMPORARY, will be personalised to user accounts later on
		oph.startJobListingsSearch();
		
	} else { //runs to allow creation of job
		
		//map coordinates, defaults to Cambridge
		var coords = { lat: 52.2100, lng: 0.1300 };
		
		//set up default map
		map = oph.map({
			api: 'gmaps',
			settings: {
				options: {
					//initial map centre location
					map_centre: coords,
					zoom: 12
				}
			}
		});
		
		//get coords on click
		$("#getClientCoords").click(function() {
			if (navigator.geolocation) {
				//get coordinates
				navigator.geolocation.getCurrentPosition(function (position) {
					//google coords object
					var gc = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
					//set form coords
					$('#centre_lat').val(gc.lat()); $('#centre_lng').val(gc.lng());
					//move map centre
					map.panTo(gc);
				});
			}
		});
		
		//bind click result to these elements
		oph.map_gmaps_bindClickCoords({ 'lat': '#centre_lat', 'lng': '#centre_lng' });
		
		//get all jobs on system - TEMPORARY, will be personalised to user accounts later on
		oph.startJobListingsSearch();
		
		//stop form from going anywhere
		$('form#job_form').submit(function (evt) {
			evt.preventDefault();
		});
		
		//validate and carry on as necessary
		$("form#job_form").validate({
			//validation rules
			rules: {
				source: "required",
				map_type: "required",
				centre_lat: {
					required: true,
					number: true,
					min: -90,
					max: 90
				},
				centre_lng: {
					required: true,
					number: true,
					min: -180,
					max: 180
				},
				radius: {
					required: true,
					number: true,
					min: 0.01,
					max: 100
				},
				filter: {
					required: false,
					minlength: 3
				}
			},
			messages: {
				//source: "Please select a source for this job.",
				//map_type: "Please select a Map for this job.",
				centre_lat: {
					required: "Please specify latitude coordinate.",
					number: "Please only use digits.",
					min: "Please enter a number higher than -90.",
					max: "Please enter a number lower than 90."
				},
				centre_lng: {
					required: "Please specify latitude coordinate.",
					number: "Please only use digits.",
					min: "Please enter a number higher than -180.",
					max: "Please enter a number lower than 180."
				},
				radius: {
					required: "Please specify a radius (miles) for this job.",
					number: "Please only use digits.",
					min: "Please enter a number higher than 0.01.",
					max: "Please enter a number lower than 100."
				},
				filter: {
					minlength: "If you require job messages to be filtered, please specify at least a 3 letter word."
				}
			},
			//form was validated
			submitHandler: function(form) {
				
				//prevent multiple submissions
				$('input[type=submit]').prop('disabled', true);
				
				//variables
				var form_data, job_info;
				
				//get form data as JSON
				form_data = $(form).serializeJSON();
	
				//set miles unit
				form_data.radius = form_data.radius + 'mi';
				
				//create job - AJAX
				job_info = oph.createJob(form_data);
				
				if ("id" in job_info) {
					
					//change location bar
					window.history.pushState(job_info.id, 'o:Pheme - Job ID: ' + job_info.id, '?' + job_info.id);
					
					//prepare for re-use
					oph.clearMapElement();
					
					//compute radius
					var radius = parseInt(job_info.radius.slice(0, -2)),
						map_zoom;
			
					switch(true) {
						case ((radius > 0) && (radius <= 1)):
							map_zoom = 14;
							break;
						case ((radius > 1) && (radius <= 3)):
							map_zoom = 13;
							break;
						case ((radius > 3) && (radius <= 7)):
							map_zoom = 12;
							break;
						case ((radius > 7) && (radius <= 10)):
							map_zoom = 11;
							break;
						case ((radius > 10) && (radius <= 20)):
							map_zoom = 10;
							break;
						case ((radius > 20) && (radius <= 50)):
							map_zoom = 9;
							break;
						default:
							map_zoom = 8;
							break;
					}
					
					//set up job map
					map = oph.map({
						api: job_info.map_type,
						settings: {
							options: {
								map_centre: {
									lat: job_info.centre_lat,
									lng: job_info.centre_lng
								},
								zoom: map_zoom
							},
							noOfMarkers: 50
						}
					});
					
					//start job - get messages, store them in queue
					oph.startJob();
					
					//start job queue
					oph.startJobQueue();
					
					//let user know
					window.alert("Job Created! Please wait up to a minute for messages to appear!");
					
				} else {
					
					if (job_info == 0) window.alert("Please do not try to hack me! Please try again.");
					
					//re-enable submit button
					$('input[type=submit]').prop('disabled', false);
					
				}
				
			}
		});
		
	}
	
});
