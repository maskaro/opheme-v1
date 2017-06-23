		
		<script type="text/javascript">
			
			var oph = new $.oPhemeUI($("#map_preview"), {
					timeout: 30000 //run every 15 sec
				}),
				coords,
				map,
				json,
				centre_marker;
				
			//map coordinates, defaults to Cambridge
			coords_default = { lat: 52.225550717210936, lng: 0.1366367567297857 };
			
			//set up default map
			map = oph.map({
				api: 'gmaps',
				settings: {
					options: {
						//initial map centre location
						map_centre: coords_default,
						zoom: 11
					}
				}
			});
			
			centre_marker = oph.map_addMarker({
				lat: coords_default.lat,
				lng: coords_default.lng,
				user: "Center of Discover",
				msg: "\<div class='profilePic'>\
							<img src='/img/phem_small.png' />\
						</div>\
						<div class='profileContainer'>\
							<div class='profileName'>\
								Centre of Discover\
							</div>\
							<div class='bubbleMessage'>\
								This is where the centre of the Discover is now set. Tweets will be monitored around this within the set radius.\
							</div>\
							<div class='bubbleTimestamp'>\
								\
							</div>\
						</div>"
			});
			centre_marker.setVisible(false);
	
			$('#campaign-editor').on('shown', function () {
				google.maps.event.trigger(map, "resize");
				map.panTo(new google.maps.LatLng(coords_default.lat, coords_default.lng));
			});
	
			//get coords on click
			$("#click_get_client_coords").click(function() {
				if (navigator.geolocation) {
					//get coordinates
					navigator.geolocation.getCurrentPosition(function (position) {
						//trigger resize
						google.maps.event.trigger(map, 'resize');
						//google coords object
						var gc = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
						//set form coords
						$('#discover_centre_lat').val(gc.lat()); $('#discover_centre_lng').val(gc.lng());
						//move map centre
						map.panTo(gc);
						//move marker
						centre_marker.setVisible(false);
						centre_marker.setPosition(new google.maps.LatLng(position.coords.latitude, position.coords.longitude));
						centre_marker.setVisible(true);
					});
				}
			});
	
			//bind click result to these elements
			oph.map_gmaps_bindClickCoords({ 'lat': '#discover_centre_lat', 'lng': '#discover_centre_lng' }, centre_marker);

			$('.modal').on('click', '#close-modal', function() {
				$('#campaign-editor').modal('toggle')
			});
			
			// datepicker http://www.eyecon.ro/bootstrap-datepicker/
			var nowTemp = new Date();
			var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
			 
			var startDiscover = $('#scd1').datepicker({ // scd start campaign date
				onRender: function(date) {
					return date.valueOf() < now.valueOf() ? 'disabled' : '';
				}
			}).on('changeDate', function(ev) {
				if (ev.date.valueOf() > endDiscover.date.valueOf()) {
					var newDate = new Date(ev.date)
					newDate.setDate(newDate.getDate() + 1);
					endDiscover.setValue(newDate);
				}
				startDiscover.hide();
				$('#ecd2')[0].focus();  // ecd end campaign date
			}).data('datepicker');
			var endDiscover = $('#ecd2').datepicker({
				onRender: function(date) {
					return date.valueOf() < startDiscover.date.valueOf() ? 'disabled' : '';
				}
			}).on('changeDate', function(ev) {
				endDiscover.hide();
			}).data('datepicker');
	  
			// tooltip helper function
			$('.more-info-tooltip').hover(function() {
				$(this).tooltip('toggle');
			});
	  
			// alert box fade out helper function
			$('.alert button.close').click(function() {
				$(this).parent().animate({opacity: 0, height: 0, padding: 0, margin: 0}, 250);
			});
			
			// date picker showing on top of the modal
			var modalZ = $('.modal').css('z-index');
			$('.datepicker').css('z-index', modalZ + 50);
			
			$('#sct1').timepicker({
				defaultTime: '09:00',
				onClose: function() {
					$('#ect2').focus();
				}
			});
			$('#ect2').timepicker({
				defaultTime: '17:00',
				onClose: function() {
					$('#scd1').focus();
				}
			});
			
			$("form#editor").validate({
				errorClass: "alert alert-error",
				validClass: "alert alert-success",
				//validation rules
				rules: {
					discover_name: "required",
					discover_filter: {
						minlength: 1
					},
					discover_filter_ex: {
						minlength: 1
					},
					discover_time_start: {
						time: true
					},
					discover_time_end: {
						time: true
					},
					discover_date_start: {
						date: true
					},
					discover_date_end: {
						date: true
					},
					discover_centre_lat: {
						required: true,
						number: true
					},
					discover_centre_lng: {
						required: true,
						number: true
					},
					discover_radius: {
						required: true,
						number: true,
						min: 0.1,
						max: 10
					}
				},
				messages: {
					discover_name: "Please enter a name for this Discover.",
					discover_filter: {
						minlength: "Please enter at least 5 characters."
					},
					discover_filter_ex: {
						minlength: "Please enter at least 5 characters."
					},
					discover_time_start: "You must specify a valid start time of the day.",
					discover_time_end: "You must specify a valid end time of the day.",
					discover_date_start: "You must specify a valid start date.",
					discover_date_end: "You must specify a valid end date.",
					discover_centre_lat: "You must specify a valid Latitude coordinate.",
					discover_centre_lng: "You must specify a valid Longitude coordinate.",
					discover_radius: "Radius for this Discover (Miles) should range between 0.1 and 10 miles."
				}
			});
			
			var tickDays = function(tick) {
				$('#editor input[name="discover_days[]"]').each(function (index, val) {
					if (this.checked) $(val).prop('checked', false);
					else $(val).prop('checked', true);
				});
				if (!tick) {
					if ($('#discover-day-all')[0].checked) $('#discover-day-all').prop('checked', false);
					else $('#discover-day-all').prop('checked', true);
				}
				$("form#editor").valid();
			}
			
			$('#tickAllDays').click(function () { tickDays(); });
			$('#discover-day-all').click(function () { tickDays(true); });
			$('#editor input[name="discover_days[]"]').click(function () {
				var sum = 0;
				$('#editor input[name="discover_days[]"]').each(function (index, val) { if (this.checked) sum++; });
				if (sum == 7) {
					$('#discover-day-all').prop('checked', true);
				} else {
					$('#discover-day-all').prop('checked', false);
				}
			});
			
			$("#constraintsActivator").click(function() { $("#toggleConstraints").slideToggle("slow"); });
			
		</script>