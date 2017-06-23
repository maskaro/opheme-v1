/*

oPheme UI - jQuery Plugin

Copyright Razvan-Ioan Dinita

BASED ON WORK FOUND AT < http://www.queness.com/post/112/a-really-simple-jquery-plugin-tutorial
http://docs.jquery.com/Plugins/Authoring http://stefangabos.ro/jquery/jquery-plugin-boilerplate-oop/ >

//for (var prop in data.options) { alert(prop + " = " + data.options[prop]); break; }

*/

//one camp per instance
;(function($) {
	
	google.maps.visualRefresh = true;

    $.oPhemeUI = function(el, options) {
		
		//default options
        var defaults = {
            omap: { //map related options
				api: 'gmaps', //must be declared
				gmaps: { 
					options: { //map specific options, must consult with API
						map_centre: { //custom container, defines map centre coords
							lat: 52.2100,
							lng: 0.1300
						},
						zoom: 14,
						mapTypeId: google.maps.MapTypeId.ROADMAP,
						panControl: false,
						zoomControl: false,
						mapTypeControl: true,
						mapTypeControlOptions: {
							style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
							position: google.maps.ControlPosition.TOP_CENTER
						},
						scaleControl: false,
						streetViewControl: false,
						overviewMapControl: false
					},
					noOfMarkers: 150, //max number of markers on map
					displayInfoWindow: false, //don't create the on-click infoWindow
					markerInfoWindowTimeout: 3000, //time to auto-close infoWindow in ms, 0 for never
					mc_options: { //marker clusterer options
						gridSize: 30,
						maxZoom: 15
					},
					precision: 7 //coords precision, digits after dot
				}
			},
			timeout: 10000, //checkDisc timeout in ms
			max_items: 10, //max number of messages to be displayed at a time
			display_freq: 10000 //message display frequency
        }

        var plugin = this; //internal reference

		//internal tracking of things
		plugin.internal = {
			_map_handle: null, //map handle
			_map_mc_handle: null, //map marker clusterer handle
			_map_markers: [], //keep track of markers
			_map_marker_tooltips: [], //keep track of marker tooltips
			_map_tooltip_class: '_opheme_bubbleContainer', //name of tooltip css class
			_map_container: $('<div class="opheme_map_"/>'), //custom container in which to display the map
			_php_connection: '/php/oPhemeUI_to_PHP_connection.php', //php script which handles ajax requests - URL, relative or absolute
			_disc_link_container: $('<div />', { id: '_opheme_disc_link'}), //custom container in which to display disc link
			_disc_info: null, //camp spec information
			_disc_updates: [], //camp messages, looks similar to plugin settings
			_disc_listings: [], //all camps on system
			_disc_timer: null, //camp timer handle
			_disc_queue_timer: null, //camp timer handle
			_disc_refresh: 1, //turns to 0 after first time camp checks for messages
			_disc_listings_initial: 1 //turns to 0 after first time camps get retrieved
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
			
			//save settings
			plugin.settings.omap.gmaps = g;
			
			//return handle for further manipulation
			return plugin.internal._map_handle;
			
		}
		
		plugin.map_gmaps_markerGetSameCoords = function(coords, self) {
			
			var ref = self || plugin,
				markers = ref.internal._map_markers,
				mCoords;
			
			if (markers.length == 0) {
				return false;
			}
			
			for (var i = 0; i < markers.length; i++) {
				mCoords = markers[i].getPosition();
				if (coords.equals(mCoords)) {
					break;
				}
			}
			
			if (i == markers.length) {
				return false;
			}
			
			return i;
			
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
				existingMarkerPosition = ref.map_gmaps_markerGetSameCoords(where, ref);
			
			if (existingMarkerPosition !== false) {
				
				var id = 'job_' + ref.internal._disc_info.id + '_marker_' + existingMarkerPosition;
				window.document.getElementById(id).innerHTML += info.msg;
				
				var marker = new google.maps.Marker({
					map: ref.internal._map_handle,
					animation: google.maps.Animation.DROP,
					position: where,
					title: info.user/*,
					icon: {
						url: 'img/user.png',
						size: new google.maps.Size(80, 80),
						origin: new google.maps.Point(0, 0),
						anchor: new google.maps.Point(0, 0)
					}*/,
					zIndex: (-ref.internal._map_markers.length)
				});
				
				//add marker to mc
				ref.internal._map_mc_handle.addMarker(marker);
				
				//keep track of markers
				ref.internal._map_markers.push(marker);
				
				return ref.internal._map_markers[existingMarkerPosition];
			}
			
			//create marker
			var marker = new google.maps.Marker({
					map: ref.internal._map_handle,
					animation: google.maps.Animation.DROP,
					position: where,
					title: info.user,
					/*,
					icon: {
						url: 'img/user.png',
						size: new google.maps.Size(80, 80),
						origin: new google.maps.Point(0, 0),
						anchor: new google.maps.Point(0, 0)
					}*/
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
			
			var id = (ref.internal._disc_info?ref.internal._disc_info.id:'0');
			
			//configure tooltip
			var tooltipOptions = {
				marker: marker,// required
				marker_id: 'job_' + id + '_marker_' + ref.internal._map_markers.length,
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
			
			//used for external purposes
			return marker;
			
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
		plugin.map_gmaps_bindClickCoords = function(info, marker) {
			
			var precision = plugin.settings.omap.gmaps.precision;
			var m = marker;
			var _map = plugin.internal._map_handle;
			
			google.maps.event.addListener(plugin.internal._map_handle, "click", function(e) {
				var coords = { 'lat': e.latLng.lat().toFixed(precision), 'lng': e.latLng.lng().toFixed(precision) }
				$(info.lat).val(coords.lat);
				$(info.lng).val(coords.lng);
				_map.panTo(new google.maps.LatLng(coords.lat, coords.lng));
				m.setVisible(false);
				m.setPosition(new google.maps.LatLng(coords.lat, coords.lng));
				m.setVisible(true);
			});
			
		}
		
		//return full camp specs based on id
		plugin.getDiscSpecs = function(id) {
			
			var disc_info = null,
				data = { 'do': 'discover_getSpecs', 'id': id };
			
			//initiate request
			$.ajax({
				async: false,
				type: 'POST',
				cache: false,
				dataType: 'json',	
				url: plugin.internal._php_connection,
				data: data
			}).done(function(msg) { //result handler
				//get camp data for a later return
				disc_info = msg;
			});
			
			//disc_info.refresh = plugin.internal._disc_refresh;
			
			plugin.internal._disc_info = disc_info;
			
			//return camp info
			return disc_info;
			
		}
		
		//get camp latest messages based on camp id
		plugin.checkDisc = function(self) {
			
			var ref = self || plugin;
			ref.internal._disc_info.refresh = ref.internal._disc_refresh; //pass on the refresh parameter
			var disc_updates = [],
				data = { 'do': 'discover_getNewMessages', 'disc_info': ref.internal._disc_info };
			
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
				disc_updates = msg;
				
			});
			
			//save the camp updates - add them to all messages relavant to this camp
			$.extend(ref.internal._disc_updates, disc_updates);
			
			//update count
			if (disc_updates instanceof Array) {
				if (disc_updates.length > 0) {
					var initial = parseInt($('#discover_' + ref.internal._disc_info.id + '_count').html());
					if (initial == 0 || ref.internal._disc_refresh == 1) {
						$('#discover_' + ref.internal._disc_info.id + '_count').html(disc_updates.length);
					} else {
						$('#discover_' + ref.internal._disc_info.id + '_count').html((initial + disc_updates.length));
					}
				}
			}
			
			if ($(".preview-placeholder.loader").is(':visible')) $(".preview-placeholder.loader").fadeOut(300);
			
			//return camp specs
			return disc_updates;
			
		}
		
		//setTimeout - check the camp every X seconds
		plugin.startDisc = function() {
			
			//get references to the required function and plugin
			var checkDiscFunction = plugin.checkDisc,
				self = plugin;
			
			//get the new messages
			var messages = checkDiscFunction(self);
			
			if (messages instanceof Array) {
				
				//first run
				if (self.internal._disc_refresh == 1) {
					
					//set refresh to 0 if initial run has already occured
					self.internal._disc_refresh = 0;
					
				}
				
			}
			
			//stop running if campaign is suspended
			if (parseInt(self.internal._disc_info.suspended) == 1) {
				self.stopDisc(self);
			}
			
			//set the timer
			plugin.internal._disc_timer = setInterval(function() {
				
				//stop running if campaign is suspended
				if (parseInt(self.internal._disc_info.suspended) == 1) {
					self.stopDisc(self);
				} else {
					//get the new messages
					checkDiscFunction(self);
				}
				
			}, plugin.settings.timeout);
			
		}
		
		//setTimeout - check the disc every X seconds
		plugin.startDiscQueue = function() {
			
			//get references to the required function and plugin
			var self = plugin;
			
			//set the timer
			plugin.internal._disc_queue_timer = setInterval(function() {
				
				//get the new messages
				var messages = self.internal._disc_updates,
					message,
					text,
					count = 0;
				
				if (messages instanceof Array && messages.length > 0) {
					
					try {
					
						do {
						
							//get message and remove it from the queue
							message = messages.shift();
							
							//put together bubble text
							text = "\<div class='profilePic'>\
											<img src='" + (!(typeof message.user == 'undefined')?message.user.profile_image_url:'/img/phem_small.png') + "' />\
										</div>\
										<div class='profileContainer'>\
											<div class='profileName'>\
												<div class='custom-follow-button'>\
													<a onclick='oph.twitterFollow(\"" + message.user.id + "\")' alt='Follow'>\
														<i class='btn-icon'></i>\
														<span class='btn-text'>Follow @" + message.user.screen_name + "</span>\
													</a>\
												</div>\
												<div class='closeButton'>\
													<img src='/img/button_close.png' onclick='$(this.parentNode.parentNode.parentNode.parentNode).css(\"visibility\", \"hidden\")' />\
												</div>\
											</div>\
											<div class='bubbleMessage'>\
												" + self.replaceURLWithHTMLLinks(message.text) + "\
											</div>\
											<div class='bubbleTimestamp'>\
												" + message.created_at + "\
											</div>\
										</div>";
							
							//create marker for each
							self.map_addMarker({
								lat: message.geo.coordinates[0],
								lng: message.geo.coordinates[1],
								user: (!(typeof message.user === 'undefined')?message.user.screen_name:'unknown_user'),
								msg: text
							}, self);
						
						} while (count++ < self.settings.max_items && messages.length > 0);
						
						//update internal message tracking
						self.internal._disc_updates = messages;
					
					} catch (err) {
						console.log('Error in startDiscQueue: ' + err.message);
						console.log(message);
					}
					
				}
				
				//stop message processing if no more messages in queue and disc is suspended
				//if (self.internal._disc_updates.length === 0 && parseInt(self.internal._disc_info.suspended) === 1) {
					//clearInterval(self.internal._disc_queue_timer);
				//}
				
			}, plugin.settings.display_freq);
			
		}
		
		//stop the disc
		plugin.stopDisc = function(self) {
			
			//get proper grasp of this
			var ref = self || plugin;
			
			//clear the camp
			clearInterval(ref.internal._disc_timer);
			
		}
		
		//stop the disc
		plugin.stopDiscQueue = function(self) {
			
			//get proper grasp of this
			var ref = self || plugin;
			
			//clear the camp
			clearInterval(ref.internal._disc_queue_timer);
			
		}
		
		plugin.pauseDisc = function() {
			
			var data = { 'do': 'discover_pause', 'disc_info': plugin.internal._disc_info };
			
			//initiate request
			$.ajax({
				async: false,
				type: 'POST',
				cache: false,
				dataType: 'json',
				url: plugin.internal._php_connection,
				data: data
			}).done(function(msg) { //result handler
				
				var alert = '<div class="alert alert-%status">Discover %message. Reloading interface, please wait...</div>';
				
				if (parseInt(msg) === 1) {
					alert = alert.replace('%status', 'success');
					alert = alert.replace('%message', 'has been successfully paused');
					$('#discover_' + plugin.internal._disc_info.id + '_status').html('<span style="color: red">No</span>');
				} else {
					alert = alert.replace('%status', 'error');
					alert = alert.replace('%message', 'has not been paused. Database error');
				}
				
				$('#dashboard').prepend(alert);
				
				setTimeout(function() {
					location.reload();
				}, 4000);
				
			});
			
		}
		
		plugin.unPauseDisc = function() {
			
			var data = { 'do': 'discover_unPause', 'disc_info': plugin.internal._disc_info };
			
			//initiate request
			$.ajax({
				async: false,
				type: 'POST',
				cache: false,
				dataType: 'json',
				url: plugin.internal._php_connection,
				data: data
			}).done(function(msg) { //result handler
				
				var alert = '<div class="alert alert-%status">Discover %message. Reloading interface, please wait...</div>';
				
				if (parseInt(msg) === 1) {
					alert = alert.replace('%status', 'success');
					alert = alert.replace('%message', 'has successfully resumed');
					$('#discover_' + plugin.internal._disc_info.id + '_status').html('<span style="color: green">Yes</span>');
				} else {
					alert = alert.replace('%status', 'error');
					alert = alert.replace('%message', 'has not resumed. Database error');
				}
				
				$('#dashboard').prepend(alert);
				
				setTimeout(function() {
					location.reload();
				}, 4000);
				
			});
			
		}
		
		plugin.twitterFollow = function(user_id) {
			
			var data = { 'do': 'discover_twitterFollow', 'user_id': user_id };
			
			//initiate request
			$.ajax({
				async: false,
				type: 'POST',
				cache: false,
				dataType: 'json',
				url: plugin.internal._php_connection,
				data: data
			}).done(function(msg) { //result handler
				
				var alert = '<div class="alert alert-%status">User has %message.</div>';
				
				if (parseInt(msg) === 1) {
					alert = alert.replace('%status', 'success');
					alert = alert.replace('%message', 'been successfully followed');
				} else {
					alert = alert.replace('%status', 'error');
					alert = alert.replace('%message', 'not been successfully followed. Already following OR Twitter error');
				}
				
				$('#dashboard').prepend(alert);
				
				setTimeout(function() {
					$('.alert.alert-success').remove();
					$('.alert.alert-error').remove();
				}, 5000);
				
			});
			
		}
		
		plugin.replaceURLWithHTMLLinks = function(text) {
			var exp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
			return text.replace(exp,"<a href='$1' target='_blank'>$1</a>"); 
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
