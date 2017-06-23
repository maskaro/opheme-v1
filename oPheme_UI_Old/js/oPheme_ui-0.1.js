/*

oPheme UI - jQuery Plugin

Copyright Razvan-Ioan Dinita

BASED ON WORK FOUND AT < http://www.queness.com/post/112/a-really-simple-jquery-plugin-tutorial
http://docs.jquery.com/Plugins/Authoring http://stefangabos.ro/jquery/jquery-plugin-boilerplate-oop/ >

//for (var prop in data.options) { alert(prop + " = " + data.options[prop]); break; }

*/

//one job per instance
;(function($) {

    $.oPhemeUI = function(el, options) {
		
		//default options
        var defaults = {
            omap: { //map related options
				api: 'gmaps', //must be declared
				gmaps: { 
					options: { //map specific options, must consult with API
						map_centre: { //custom container, defines map centre coords
							lat: -34.397,
							lng: 150.644
						},
						zoom: 8,
						mapTypeId: google.maps.MapTypeId.ROADMAP,
						panControl: true,
						zoomControl: true,
						mapTypeControl: true,
						scaleControl: true,
						streetViewControl: true,
						overviewMapControl: true
					},
					noOfMarkers: 200, //max number of markers on map
					displayInfoWindow: false, //don't create the on-click infoWindow
					markerInfoWindowTimeout: 3000, //time to auto-close infoWindow in ms, 0 for never
					mc_options: { //marker clusterer options
						gridSize: 30,
						maxZoom: 15
					},
					precision: 7 //coords precision, digits after dot
				}
			},
			timeout: 10000, //checkJob timeout in ms
			max_items: 10, //max number of messages to be displayed at a time
			display_freq: 10000 //message display frequency
        }

        var plugin = this; //internal reference

		//internal tracking of things
		plugin.internal = {
			_map_handle: null, //map handle
			_map_mc_handle: null, //map marker clusterer handle
			_map_mc_iw: null, //mc info window handle
			_map_markers: [], //keep track of markers
			_map_marker_tooltips: [], //keep track of marker tooltips
			_map_tooltip_class: '_opheme_bubbleContainer', //name of tooltip css class
			_map_container: $('<div />', { id: '_opheme_map'}), //custom container in which to display the map
			_php_connection: 'php/oPhemeUI_to_PHP_connection.php', //php script which handles ajax requests - URL, relative or absolute
			_job_link_container: $('<div />', { id: '_opheme_job_link'}), //custom container in which to display job link
			_job_info: null, //job spec information
			_job_updates: [], //job messages, looks similar to plugin settings
			_job_listings: [], //all jobs on system
			_job_timer: null, //job timer handle
			_job_refresh: 1, //turns to 0 after first time job checks for messages
			_job_listings_initial: 1 //turns to 0 after first time jobs get retrieved
		}
		
        plugin.settings = {} //public settings

        var init = function() { //initial setup
            
			//add custom settings to defaults, overriding as necessary
			plugin.settings = $.extend({}, defaults, options);
            //jquery element reference
			plugin.el = el;
			//add container to view
			plugin.el.append(plugin.internal._map_container);
			
        }
		
		//empty the map element, ready to re-use
		plugin.clearMapElement = function() { plugin.internal._map_container.empty(); }
		
		//generic map setup, will point to map_<custom_API>()
		plugin.map = function(info) {
			
			plugin.settings.omap.api = info.api;
			
			eval("var map = plugin.map_" + info.api + "(info.settings)");
			
			return map;
			
		}
		
		//generic add marker, will point to map_<custom_API>_addMarker()
		plugin.map_addMarker = function(info, self) {
			
			eval("var marker = plugin.map_" + plugin.settings.omap.api + "_addMarker(info, self)");
			
			return marker;
		
		}
		
		//generic add marker, will point to map_<custom_API>_clearMarker()
		plugin.map_clearMarker = function(info, self) {
			
			eval("plugin.map_" + plugin.settings.omap.api + "_clearMarker(info, self)");
		
		}
		
		//generic add marker, will point to map_<custom_API>_closeInfoWindow()
		plugin.map_closeInfoWindow = function(info, self) {
			
			eval("plugin.map_" + plugin.settings.omap.api + "_closeInfoWindow(info, self)");
		
		}
		
		//generic get click coords, will point to map_<custom_API>_getClickCoords()
		plugin.map_getClickCoords = function(info) {
			
			eval("plugin.map_" + plugin.settings.omap.api + "_bindClickCoords(info)");
		
		}
		
		/* GOOGLE MAPS SPECIFIC METHODS */
		
		//google maps initial setup
		plugin.map_gmaps = function(settings) {
			
			var g = plugin.settings.omap.gmaps;
			
			if (settings) { g = $.extend(true, g, settings); }
			
			//create centre of GMaps view
			g.options.center = new google.maps.LatLng(g.options.map_centre.lat, g.options.map_centre.lng);
			
			//initialise map
			plugin.internal._map_handle = new google.maps.Map(plugin.internal._map_container[0], g.options);
			
			//initialise mc
			plugin.internal._map_mc_handle = new MarkerClusterer(plugin.internal._map_handle, [], g.mc_options);
			
			//create info window
			plugin.internal._map_mc_iw = new google.maps.InfoWindow();
			
			//setup mc tooltip
			google.maps.event.addListener(plugin.internal._map_mc_handle, "mouseover", function (mCluster) {    
				plugin.internal._map_mc_iw.content += "<div id='cluster_tooltip'>Messages in this Cluster: <bold>" + mCluster.getSize() + "</bold><br /><br />Click to zoom in.<\/div>";
				plugin.internal._map_mc_iw.setPosition(mCluster.getCenter());
				plugin.internal._map_mc_iw.open(plugin.internal._map_handle);
			});
			
			google.maps.event.addListener(plugin.internal._map_mc_handle, "mouseout", function (mCluster) {    
				plugin.internal._map_mc_iw.close();
			});
			
			//save settings
			plugin.settings.omap.gmaps = g;
			
			//return handle for further manipulation
			return plugin.internal._map_handle;
			
		}
		
		//google maps add marker
		// info: { lat, lng, user, msg }
		plugin.map_gmaps_addMarker = function(info, self) {
			
			var ref = self || plugin,
				g = ref.settings.omap.gmaps;
			
			//marker limit reached
			if (ref.internal._map_markers.length == g.noOfMarkers) {
				//remove first marker
				ref.map_gmaps_clearMarker(0, ref);
			}
			
			//get position of marker
			var where = new google.maps.LatLng(info.lat, info.lng),
			//create marker
				marker = new google.maps.Marker({
					map: ref.internal._map_handle,
					animation: google.maps.Animation.DROP,
					position: where,
					title: info.user
				});
			
			//if infoWindow needs to be created
			if (g.displayInfoWindow) {
				
				//create infoWindow, contains lots of text
				marker.infoWindow = new google.maps.InfoWindow({
					content: info.msg
				});
				
				//bind to click function on marker, show/hide infoWindow
				google.maps.event.addListener(marker, 'click', function() {
					if (!marker.infoWindow.getMap()) {
						//open infoWindow
						marker.infoWindow.open(ref.internal._map_handle, marker);
						//set timer to close, if needed
						if (ref.settings.omap.gmaps.markerInfoWindowTimeout > 0) {
							setTimeout(function(marker) {
								marker.infoWindow.close();
							}, ref.settings.omap.gmaps.markerInfoWindowTimeout, marker);
						}
					}
					else
						marker.infoWindow.close();
				});
				
			}
			
			//configure tooltip
			var tooltipOptions = {
				marker: marker,// required
				content: info.msg,// required
				cssClass: ref.internal._map_tooltip_class // name of a css class to apply to tooltip
			},
			
			//create tooltip
				tooltip = new Tooltip(tooltipOptions);
			
			//add marker to mc
			ref.internal._map_mc_handle.addMarker(marker);
			
			//keep track of markers
			ref.internal._map_markers.push(marker);
			
			//keep track of tooltips
			ref.internal._map_marker_tooltips.push(tooltip);
			
		}
		
		//google maps clear marker
		plugin.map_gmaps_clearMarker = function(id, self) {
			
			var ref = self || plugin,
				markers = ref.internal._map_markers;
			
			//pre check for user mistakes
			if (markers.length == 0 || id >= markers.length) {
				//$.error('Marker id is greater than total markers. id=' + id + ', total=' + markers.length);
				return;
			}
			
			//marker handle
			var m;
			
			if (id !== undefined) { //id given
				//get marker handle
				m = markers[id];
				//remove marker
				m.setMap(null);
				//remove marker from tracking array
				markers.splice(id, 1);
			} else { //no id given
				//get marker handle and remove it from tracking array
				m = markers.shift();
				//remove marker
				m.setMap(null);
			}
			
			//update internal tracking of markers
			ref.internal._map_markers = markers;
			
		}
		
		//google maps close infoWindow
		plugin.map_gmaps_closeInfoWindow = function(id, self) {
			
			var ref = plugin || self,
				markers = ref.internal._map_markers;
			
			//pre check for user mistakes
			if (markers.length == 0 || id >= markers.length) {
				$.error('Marker id is greater than total markers. id=' + id + ', total=' + markers.length);
				return;
			}
			
			if (id) { //id given
				//close infoWindow
				markers[id].infoWindow.close();
			} else { //no id given
				//get marker handle
				var m = markers[0];
				//close infoWindow
				m.infoWindow.close();
			}
		}
		
		/**/
		//google maps get click coords
		plugin.map_gmaps_bindClickCoords = function(info) {
			
			var precision = plugin.settings.omap.gmaps.precision;
			
			google.maps.event.addListener(plugin.internal._map_handle, "click", function(e) {
				var coords = { 'lat': e.latLng.lat().toFixed(precision), 'lng': e.latLng.lng().toFixed(precision) }
				$(info.lat).val(coords.lat);
				$(info.lng).val(coords.lng);
			});
			
		}
		/**/
		
		//create job based on form data - AJAX
		plugin.createJob = function(form) {
			
			var job_info = null,
				data = { 'do': 'createJob', 'form': form };
			
			//initiate request
			$.ajax({
				async: false,
				type: 'POST',
				cache: false,
				dataType: 'json',	
				url: plugin.internal._php_connection,
				data: data
			}).done(function(msg) { //result handler
				//get job data for a later return
				job_info = msg;
			});
			
			plugin.internal._job_info = job_info;
			
			//return job info
			return job_info;
			
		}
		
		//return full job specs based on id - should be used when page loads - http://ADDRESS/<id>
		plugin.getJobSpecs = function(id) {
			
			var job_info = null,
				data = { 'do': 'getJobSpecs', 'id': id };
			
			//initiate request
			$.ajax({
				async: false,
				type: 'POST',
				cache: false,
				dataType: 'json',	
				url: plugin.internal._php_connection,
				data: data
			}).done(function(msg) { //result handler
				//get job data for a later return
				job_info = msg;
			});
			
			job_info.refresh = plugin.internal._job_refresh;
			
			plugin.internal._job_info = job_info;
			
			//return job info
			return job_info;
			
		}
		
		//get job latest messages based on job id
		plugin.checkJob = function(self) {
			
			var ref = self || plugin;
			ref.internal._job_info.refresh = ref.internal._job_refresh; //pass on the refresh parameter
			var job_updates = [],
				data = { 'do': 'checkJob', 'job_info': ref.internal._job_info };
			
			//initiate request
			$.ajax({
				async: false,
				type: 'POST',
				cache: false,
				dataType: 'json',
				url: ref.internal._php_connection,
				data: data
			}).done(function(msg) { //result handler
				
				//get specs for a later return
				job_updates = msg;
				
			});
			
			//save the job updates - add them to all messages relavant to this job
			$.extend(ref.internal._job_updates, job_updates);
			
			//return job specs
			return job_updates;
			
		}
		
		//get job latest messages based on job id
		plugin.getJobListings = function(self) {
			
			var ref = self || plugin,
				job_listings = null,
				data = { 'do': 'getJobListings' };
			
			//initiate request
			$.ajax({
				async: false,
				type: 'POST',
				cache: false,
				dataType: 'json',
				url: ref.internal._php_connection,
				data: data
			}).done(function(msg) { //result handler
				
				//get specs for a later return
				job_listings = msg;
				
			});
			
			//return job specs
			return job_listings;
			
		}
		
		//setTimeout - check the job every X seconds
		plugin.startJob = function() {
			
			//get references to the required function and plugin
			var checkJobFunction = plugin.checkJob,
				self = plugin;
			
			//set the timer
			plugin.internal._job_timer = setInterval(function() {
				
				//get the new messages
				var messages = checkJobFunction(self);
				
				if (messages instanceof Array) {
					
					//first run
					if (self.internal._job_refresh == 1) {
						
						//set refresh to 0 if initial run has already occured
						self.internal._job_refresh = 0;
						
					}
					
				}
				
			}, plugin.settings.timeout);
			
		}
		
		/*
			{"created_at":"Wed,06Mar201301:04:21+0000",
			"from_user":"harlie_soares",
			"from_user_id":74985271,
			"from_user_id_str":"74985271",
			"from_user_name":"harls",
			"geo":{"coordinates":[52.20633257650976,0.1201255805791543],"type":"Point"},
			"id":309107149164920832,
			"id_str":"309107149164920832",
			"iso_language_code":"en",
			"metadata":{"result_type":"recent"},
			"place":{"full_name":"Cambridge","id":"e0a47a1daac8224e","type":"CITY"},
			"profile_image_url":"http://a0.twimg.com/profile_images/3337938157/98d639ccecb65d33d15fcd2a4fc63073_normal.jpeg",
			"profile_image_url_https":"https://si0.twimg.com/profile_images/3337938157/98d639ccecb65d33d15fcd2a4fc63073_normal.jpeg",
			"source":"&lt;ahref=&quot;http://twitter.com/download/iphone&quot;&gt;TwitterforiPhone&lt;/a&gt;",
			"text":"Imissyouð..."}
		*/
		
		//setTimeout - check the job every X seconds
		plugin.startJobQueue = function() {
			
			//get references to the required function and plugin
			var self = plugin;
			
			//set the timer
			plugin.internal._job_timer = setInterval(function() {
				
				//get the new messages
				var messages = self.internal._job_updates,
					message,
					text,
					count = 0;
				
				if (messages instanceof Array) {
					
					do {
					
						//get message and remove it from the queue
						message = messages.shift();
						
						//put together bubble text
						text = "\<div class='profilePic'>\
										<img src='" + (("profile_image_url" in message.user)?message.user.profile_image_url:"") + "' />\
									</div>\
									<div class='profileContainer'>\
										<div class='profileName'>\
											" + message.user.screen_name + "\
										</div>\
										<div class='bubbleMessage'>\
											" + message.text + "\
										</div>\
										<div class='bubbleTimestamp'>\
											" + message.created_at + "\
										</div>\
									</div>";
						
						//create marker for each
						self.map_addMarker({
							lat: message.geo.coordinates[0],
							lng: message.geo.coordinates[1],
							user: message.user.screen_name,
							msg: text
						}, self);
					
					} while (count++ < self.settings.max_items);
					
					//update internal message tracking
					self.internal._job_updates = messages;
					
				}
				
			}, plugin.settings.display_freq);
			
		}
		
		//setTimeout - check for new jobs every X seconds
		plugin.startJobListingsSearch = function() {
			
			//get references to the required function and plugin
			var getJobListingsFunction = plugin.getJobListings,
				self = plugin;
			
			//set the timer
			plugin.internal._job_timer = setInterval(function() {
				
				//get all jobs
				var jobs = getJobListingsFunction(self);
				
				//if there are any
				if (jobs instanceof Array) {
					
					//parse them and add to list
					$.each(jobs, function(i, item) {
						
						//if job is new, carry on displaying it
						if (isJobNew(item, self.internal._job_listings)) {
						
							$('#job_listings').append("<div class='listing' id='" + item.id + "'><a>" + item.id + "</a></div>");
							$('#' + item.id).append("<div class='job_details'></div>");
							$('#' + item.id + ' .job_details').append("<a href='/?" + item.id + "' target='_blank'>Link to Job</a><br />");
							for (var j in item) { if (j.indexOf("id") < 0) $('#' + item.id + ' .job_details').append("" + j + ": " + item[j] + "<br />"); }
							
							//start off hidden
							$('#' + item.id + ' .job_details').hide();
							$('#' + item.id + ' a').click(function () {
								//hide all
								$('.job_details').each(function (i, item) { $(item).hide(); });
								//show relevant
								$('#' + item.id + ' .job_details').show();
							});
						
						}
						
					});
					
				}
				
				//save the job listing updates, keep track of all of them
				$.extend(self.internal._job_listings, jobs);
				
			}, plugin.settings.timeout);
			
		}
		
		//stop the job
		plugin.stopJob = function(self) {
			
			//get proper grasp of this
			var ref = self || plugin;
			
			//clear the job
			clearInterval(ref.internal._job_timer);
			
		}
		
		//returns bool
		var isJobNew = function (job, jobArray) {
			
			for (var i in jobArray) {
				
				if (jobArray[i].id.indexOf(job.id) != -1) return false;
				
			}
			
			return true;
			
		}
		
		//check if a string is JSON - http://stackoverflow.com/questions/4295386/how-can-i-check-if-a-value-is-a-json-object
		plugin.isJsonString = function (str) {
			
			//attempt to parse the json
			var ret = $.parseJSON(str);
			
			//is it json?
			if(typeof ret == 'object') return true;
			
			//no
			return false;
		
		}
		
		//initiate plugin instance setup
        init();

    }
	
	//http://api.jquery.com/serializeArray/ - comment from Arjen Oosterkamp
	$.fn.serializeJSON = function() {
		var json = {};
		jQuery.map($(this).serializeArray(), function(n, i){
			json[n['name']] = n['value'];
		});
		return json;
	};

})(jQuery);
