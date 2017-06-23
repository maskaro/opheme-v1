		
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
				user: "Center of Campaign",
				msg: "\<div class='profilePic'>\
							<img src='/img/phem_small.png' />\
						</div>\
						<div class='profileContainer'>\
							<div class='profileName'>\
								Centre of Campaign\
							</div>\
							<div class='bubbleMessage'>\
								This is where the centre of the Campaign is now set. Tweets will be monitored around this within the set radius.\
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
						$('#campaign_centre_lat').val(gc.lat()); $('#campaign_centre_lng').val(gc.lng());
						//move map centre
						map.panTo(gc);
						//move marker
						centre_marker.setVisible(false);
						centre_marker.setPosition(new google.maps.LatLng(position.coords.latitude, position.coords.longitude));
						centre_marker.setVisible(true);
						$("form#editor").valid();
					});
				}
			});
	
			//bind click result to these elements
			oph.map_gmaps_bindClickCoords({ 'lat': '#campaign_centre_lat', 'lng': '#campaign_centre_lng' }, centre_marker);

			$('.modal').on('click', '#close-modal', function() {
				$('#campaign-editor').modal('toggle')
			});
			
			// datepicker http://www.eyecon.ro/bootstrap-datepicker/
			var nowTemp = new Date();
			var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
			 
			var startCampaign = $('#scd1').datepicker({ // scd start campaign date
				onRender: function(date) {
					return date.valueOf() < now.valueOf() ? 'disabled' : '';
				}
			}).on('changeDate', function(ev) {
				if (ev.date.valueOf() > endCampaign.date.valueOf()) {
					var newDate = new Date(ev.date)
					newDate.setDate(newDate.getDate() + 1);
					endCampaign.setValue(newDate);
				}
				$("form#editor").valid();
				startCampaign.hide();
				$('#ecd2').focus();  // ecd end campaign date
			}).data('datepicker');
			var endCampaign = $('#ecd2').datepicker({
				onRender: function(date) {
					return date.valueOf() < startCampaign.date.valueOf() ? 'disabled' : '';
				}
			}).on('changeDate', function(ev) {
				$("form#editor").valid();
				endCampaign.hide();
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
			
			$('#editor input[name=campaign_time_start]').change(function() { $("form#editor").valid(); });
			$('#editor input[name=campaign_time_end]').change(function() { $("form#editor").valid(); });
			
			var wizardFirst = function() { $('.modal-footer').hide(); $('[id^=wizard_]').each(function(index, el) { if (index !== 0) $(el).hide(); else $(el).show('slow'); }); }
			
			var formValidator = $("form#editor").validate({
				errorClass: "alert alert-error",
				validClass: "alert alert-success",
				errorPlacement: function(error, element) {
					element.before(error);
				},
				//validation rules
				rules: {
					campaign_name: {
						required: true,
						minlength: 5
					},
					campaign_category: {
						selectcheck: true
					},
					campaign_text: {
						required: true,
						minlength: 5
					},
					campaign_response_text: {
						required: true,
						minlength: 10,
						maxlength: 55,
						containsPercentR: true,
						containsPercentC: true
					},
					campaign_filter: {
						minlength: 1
					},
					campaign_filter_ex: {
						minlength: 1
					},
					'campaign_days[]': "required",
					campaign_time_start: {
						required: true,
						time: true
					},
					campaign_time_end: {
						required: true,
						time: true
					},
					campaign_date_start: {
						required: true,
						date: true
					},
					campaign_date_end: {
						required: true,
						date: true
					},
					campaign_centre_lat: {
						required: true,
						number: true
					},
					campaign_centre_lng: {
						required: true,
						number: true
					},
					campaign_radius: {
						required: true,
						number: true,
						min: 0.1,
						max: 1
					}
				},
				messages: {
					campaign_name: "Please enter a name for this Campaign, at least 5 characters.",
					campaign_category: "Please select a Category for this Campaign.",
					campaign_text: {
						required: "Your Campaign Tweet will contain a link to this message.",
						minlength: "Please write at least 5 characters."
					},
					campaign_response_text: {
						required: "This is the Tweet your recipients will receive.",
						minlength: "Please write at least 10 characters.",
						maxlength: "Please write up to 55 characters.",
						containsPercentR: "You MUST use the %r in your Tweet.",
						containsPercentC: "You MUST use the %c in your Tweet."
					},
					campaign_filter: {
						minlength: "Please enter at least 5 characters."
					},
					campaign_filter_ex: {
						minlength: "Please enter at least 5 characters."
					},
					'campaign_days[]': "At least one day must be selected.",
					campaign_time_start: "You must specify a valid start time of the day.",
					campaign_time_end: "You must specify a valid end time of the day.",
					campaign_date_start: "You must specify a valid start date.",
					campaign_date_end: "You must specify a valid end date.",
					campaign_centre_lat: "You must specify a valid Latitude coordinate.",
					campaign_centre_lng: "You must specify a valid Longitude coordinate.",
					campaign_radius: "You must specify a valid radius for this Campaign (Miles), ranging from 0.1 to 1 mile."
				},
				ignore: ''
			});
			
			var tickDays = function(tick) {
				$('#editor input[name="campaign_days[]"]').each(function (index, val) {
					if (this.checked) $(val).prop('checked', false);
					else $(val).prop('checked', true);
				});
				if (!tick) {
					if ($('#campaign-day-all')[0].checked) $('#campaign-day-all').prop('checked', false);
					else $('#campaign-day-all').prop('checked', true);
				}
				$("form#editor").valid();
			}
			
			$('#tickAllDays').click(function () { tickDays(); });
			$('#campaign-day-all').click(function () { tickDays(true); });
			$('#editor input[name="campaign_days[]"]').click(function () {
				var sum = 0;
				$('#editor input[name="campaign_days[]"]').each(function (index, val) { if (this.checked) sum++; });
				if (sum == 7) {
					$('#campaign-day-all').prop('checked', true);
				} else {
					$('#campaign-day-all').prop('checked', false);
				}
			});
			
			$('#campaign_text').keyup(function() {
				$('#campaign_preview_text').html($(this).val());
			});
			
			/*$('#campaign_filter').keyup(function() {
				$('#campaign_preview_filter').html($(this).val());
			});*/
			
			$('#campaign_response_text').keyup(function() {
				var chars = $(this).val(), max_length = 55;
				if (max_length - chars.length == -1) $('#campaign_response_text_count').html('0');
				else $('#campaign_response_text_count').html((max_length - chars.length));
				if (chars.length > max_length) {
					$(this).val(chars.substring(0, max_length));
				}
				if (chars.indexOf('%r') == -1) {
					$('#campaign_response_text_percent_r').attr('style', 'color: red');
				} else {
					$('#campaign_response_text_percent_r').attr('style', 'color: rgb(73, 73, 73)');
				}
				if (chars.indexOf('%c') == -1) {
					$('#campaign_response_text_percent_c').attr('style', 'color: red');
				} else {
					$('#campaign_response_text_percent_c').attr('style', 'color: rgb(73, 73, 73)');
				}
				var preview = chars.replace('%r', '@Username');
				preview = preview.replace('%c', '<?php echo @$_SESSION['user']['business_type']; ?>');
				$('#campaign_response_preview').html(preview + ' <a>http://ophe.me/52381b3dd474b6.32985547</a>');
			});
			
			//preview banner
			if (window.File && window.FileReader && window.FileList) {
				$("#campaign_banner").change(function() {
					var input = this;
					if (input.files && input.files[0]) {
						var reader = new FileReader();
						reader.onload = function (e) {
							$('#campaign_preview_banner').attr('src', e.target.result);
							$('#campaign_preview_banner_container').show('slow');
						}
						reader.readAsDataURL(input.files[0]);
					}
				});
			}
			
			//wizard
			var sections = 	['info',			'filters',		'user',					'campaign', 		'running', 			'location'];
			var titles = 	['General Info',	'Search Terms',	'Recipient Message', 	'Campaign Message', 'Date and Time', 	'Location'];
			
			var wizardify = function() {
				
				//var nav_top_style = 'left: 0; top:0; position:fixed; z-index:150; _position:absolute; _top:expression(eval(document.documentElement.scrollTop+(document.documentElement.clientHeight-this.offsetHeight))); padding-top: 5px; background-color: black; width: 100%',
					//nav_bottom_style = 'left: 0; bottom:0; position:fixed; z-index:150; _position:absolute; _top:expression(eval(document.documentElement.scrollTop+(document.documentElement.clientHeight-this.offsetHeight))); padding-top: 5px; background-color: black; width: 100%',
				var nav_bottom_style = 'padding-top: 5px;',
					nav_header = '<div class="pagination pagination-small pagination-centered" style="%nav_style"><ul>',
					nav_footer = '</ul></div>',
					a_width = '155px',
					nav_current = '<li><a style="width: ' + a_width + '; font-size: 16px" href="#"><strong>%currTitle</strong></a></li>',
					nav_start = nav_header + nav_current + '<li onclick="return wizardNext(this)" data-nextsection="%next" data-currentsection="%currNext"><a style="width: ' + a_width + ';" href="#">%nextTitle <img src="/img/arrow-4_1.png" alt="Next Section" style="margin-top: 3px; float: right" /></a></li>' + nav_footer,
					nav_mid = nav_header + '<li onclick="return wizardPrev(this)" data-prevsection="%prev" data-currentsection="%currPrev"><a style="width: ' + a_width + ';" href="#"><img src="/img/arrow-4_2.png" alt="Previous Section" style="margin-top: 3px; float: left" /> %prevTitle</a></li>' + nav_current + '<li onclick="return wizardNext(this)" data-nextsection="%next" data-currentsection="%currNext"><a style="width: ' + a_width + ';" href="#">%nextTitle <img src="/img/arrow-4_1.png" alt="Next Section" style="margin-top: 3px; float: right" /></a></li>' + nav_footer,
					nav_end = nav_header + '<li onclick="return wizardPrev(this)" data-prevsection="%prev" data-currentsection="%currPrev" data-finalsection="yes"><a style="width: ' + a_width + ';" href="#"><img src="/img/arrow-4_2.png" style="margin-top: 3px; float: left" alt="Previous Section" /> %prevTitle</a></li>' + nav_current + nav_footer;
				
				$('[id^=wizard_]').each(function(index, el) {
					
					var nav, nav_top, nav_bottom;
					
					switch (index) {
						case 0:
							nav = nav_start.replace('%next', sections[1]);
							nav = nav.replace('%nextTitle', titles[1]);
							nav = nav.replace('<ul>', '<ul style="padding-left: ' + (parseInt(a_width) + 20) + 'px">');
							break;
						case (sections.length - 1):
							nav = nav_end.replace('%prev', sections[sections.length - 2]);
							nav = nav.replace('%prevTitle', titles[titles.length - 2]);
							nav = nav.replace('<ul>', '<ul style="padding-right: ' + (parseInt(a_width) + 20) + 'px">');
							break;
						default:
							nav = nav_mid.replace('%next', sections[index + 1]);
							nav = nav.replace('%nextTitle', titles[index + 1]);
							nav = nav.replace('%prev', sections[index - 1]);
							nav = nav.replace('%prevTitle', titles[index - 1]);
							break;
					}
					
					nav = nav.replace('%currTitle', titles[index]);
					nav = nav.replace('%currPrev', sections[index]);
					nav = nav.replace('%currNext', sections[index]);
					
					//nav_top = nav.replace('%nav_style', nav_top_style);
					//$(el).prepend(nav_top);
					nav_bottom = nav.replace('%nav_style', nav_bottom_style);
					$(el).append(nav_bottom);
					
					$(el).attr('style', 'width: 560px');
					
					if (index !== 0) $(el).hide();
					
				});
				
			}
			
			var formFields = {
				section_info: [ 'campaign_name', 'campaign_category' ],
				section_filters: [ 'campaign_filter', 'campaign_filter_ex' ],
				section_user: [ 'campaign_response_text' ],
				section_campaign: [ 'campaign_text' ],
				section_running: [ 'campaign_days[]', 'campaign_time_start', 'campaign_time_end', 'campaign_date_start', 'campaign_date_end' ],
				section_location: [ 'campaign_centre_lat', 'campaign_centre_lng', 'campaign_radius' ]
			};
			
			var sectionIsClear = function(id, errors) {
				var check = eval('formFields.section_' + id);
				for (var i = 0; i < check.length; i++) {
					if (check[i] in errors) {
						return false;
					}
				}
				return true;
			}
			
			var wizardNext = function(el) {
				$("form#editor").valid();
				var nextSection = $(el).data('nextsection'), currentSection = $(el).data('currentsection'),
					formErrorFields = $("form#editor").validate().errorMap;
				if (sectionIsClear(currentSection, formErrorFields) === false) {
					return false;
				}
				$('#wizard_' + currentSection).hide('slow');
				$('#wizard_' + nextSection).show('slow');
				$("html, body").animate({ scrollTop: 0 }, "slow");
				if (nextSection === 'location') {
					google.maps.event.trigger(map, "resize");
					map.panTo(new google.maps.LatLng(coords_default.lat, coords_default.lng));
				}
				
				if ($('#wizard_' + nextSection + ' div ul li').filter(':first').data('finalsection') === 'yes') {
					$('.modal-footer').show();
				}
				return false;
			}
			
			var wizardPrev = function(el) {
				var prevSection = $(el).data('prevsection'), currentSection = $(el).data('currentsection');
				$('#wizard_' + currentSection).hide('slow');
				$('#wizard_' + prevSection).show('slow');
				if ($('.modal-footer').is(':visible')) {
					$('.modal-footer').hide();
				}
				$("html, body").animate({ scrollTop: 0 }, "slow");
				return false;
			}
			
			wizardify();
			
		</script>