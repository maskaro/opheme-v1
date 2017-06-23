		
		<script type="text/javascript">
		
			// overwrite bootstrap spacing
			var rowWidth = $("#dashboard .row-fluid").width();
			var campaignListWidth = $("#campaign-list").width();
			
			var dashboardHeight = $("#dashboard .row-fluid").height();
			
			var trueWidth = rowWidth - campaignListWidth -1;
			
			/* THIS WORKS AS IS, DON'T TOUCH THIS FUNCTION */
			function set_map_styles(id) {
				if (window.orientation == undefined) { window.orientation = 0; }
				if (screen.width >= 768 && screen.width <= 1024 && window.orientation !== 0) { //iPad landscape
					eval('$("' + id + '").css("width", "950px");');
					eval('$("' + id + '").css("height", "640px");');
				} else if (screen.width >= 768 && screen.width <= 1024 && window.orientation === 0) { //iPad portrait
					eval('$("' + id + '").css("width", "730px");');
					eval('$("' + id + '").css("height", "640px");');
				} else if (screen.width >= 320 && screen.width <= 568 && window.orientation !== 0) { // iPhone landscape
					eval('$("' + id + '").css("width", "567px");');
					eval('$("' + id + '").css("height", "300px");');
				} else if (screen.width >= 320 && screen.width <= 568 && window.orientation === 0) { // iPhone portrait
					eval('$("' + id + '").css("width", "310px");');
					eval('$("' + id + '").css("height", "490px");');
				} else { // Desktop
					eval('$("' + id + '").css("width", "1170px");');
					eval('$("' + id + '").css("height", "640px");');
				}
				if (id !== '.gm-style > :first-child') {
					eval('$("' + id + '").css("position", "absolute");');
					eval('$("' + id + '").css("top", "0px");');
					eval('$("' + id + '").css("z-index", "998");');
				}
			}
			
			if (screen.width > 568) {
				$("#campaign-preview div").width(trueWidth);
				$("#campaign-preview div").height(dashboardHeight);
			} else if ((screen.width >= 320 && screen.width <= 568) || (screen.width >= 768 && screen.width <= 1024)) { // iPhone and iPad
				set_map_styles("#campaign-preview");
				set_map_styles(".campaign-map");
				set_map_styles(".opheme_map_");
				set_map_styles(".gm-style");
				set_map_styles(".gm-style > :first-child");
			}
			
			if (screen.width >= 320 && screen.width <= 568) {
				$("#campaign-preview").hide();
			}
			
			//keep both columns height even
			var currentTallest = 0,
				currentRowStart = 0,
				rowDivs = new Array(),
				$el,
				topPosition = 0;
			
			$('#dashboard .span6').each(function() {
			
				$el = $(this);
				topPostion = $el.position().top;
				
				if (currentRowStart != topPostion) {
			
					// we just came to a new row.  Set all the heights on the completed row
					for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
						rowDivs[currentDiv].height(currentTallest);
					}
			
					// set the variables for the new row
					rowDivs.length = 0; // empty the array
					currentRowStart = topPostion;
					currentTallest = $el.height();
					rowDivs.push($el);
			
				} else {
			
					// another div on the current row.  Add it to the list and check if it's taller
					rowDivs.push($el);
					currentTallest = (currentTallest < $el.height()) ? ($el.height()) : (currentTallest);
			
				}
				 
				// do the last row
				for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
					rowDivs[currentDiv].height(currentTallest);
				}
				
			});
			
			$('#campaign-table tbody').on('click', 'tr.campaign i.icon-pause', function() {
				
				var id;
				
				//show current
				id = $(this).closest('tr').attr('id');
				
				eval("oph_" + id + ".stopCampQueue();");
				eval("oph_" + id + ".pauseCamp();");
				
				$(this).hide(); //hide current icon
				$(this).next().show(); //show next icon
				
			});
			
			$('#campaign-table tbody').on('click', 'tr.campaign i.icon-play', function() {
				
				var id;
				
				//show current
				id = $(this).closest('tr').attr('id');
				
				eval("oph_" + id + ".startCampQueue();");
				eval("oph_" + id + ".unPauseCamp();");
				
				$(this).hide(); //hide current icon
				$(this).prev().show(); //show previous icon
				
			});
			
			$('#campaign-table tbody').on('click', 'tr.campaign i.icon-search', function() {
				
				if (screen.width >= 320 && screen.width <= 568) $("#campaign-preview").show();
				
				var id;
				
				//fade all
				$('#campaign-table').find('tr').each(function (index) {
					id = $(this).attr('id');
					if (typeof id !== 'undefined') $("#map_" + id).fadeOut(300);
				});
				
				//show current
				id = $(this).closest('tr').attr('id');
				$("#map_" + id).fadeIn(300);
				eval("google.maps.event.trigger(map_" + id + ", 'resize')");
				eval("map_" + id + ".panTo(new google.maps.LatLng(coords_" + id + ".lat, coords_" + id + ".lng))");
				setTimeout('eval("map_' + id + '.setZoom(10)")', 1000);
				
				//hide placeholder
				$(".preview-placeholder.icon").fadeOut(300);
				$("#map_cc").fadeIn(300);
				
			});
			
			$('#campaign-table tbody').on('click', 'tr.campaign i.icon-remove', function() {
				var result = confirm("Are you sure you want to delete this campaign?");
				if (result==true) {
					var id = $(this).closest('tr').attr('id');
					var thenum = id.replace( /^\D+/g, '');
					$('<form action="/campaign-process" method="post"><input type="hidden" name="campaign_id" value="' + thenum + '" /><input type="hidden" name="campaign_delete" value="1" /></form>').appendTo('body').submit();
				}
			});
			
			$('.nav').on('click', '#new-campaign-nav', function() {
			  
				$('#editor input[name=campaign_name]').val('');
				$("#editor select[name=campaign_category] option[value='----------']").prop("selected", "selected");
				$('#editor textarea[name=campaign_text]').val(''); $('#campaign_preview_text').html('(no message set yet)');
				$('#editor input[name=campaign_response_text]').val('');
				$('#campaign_preview_banner').attr('src', '/img/banner_placeholder.png');
				$('#editor input[name=campaign_filter]').val(''); $('#campaign_preview_filter').html('(no keyword set yet)');
				$('#editor input[name=campaign_filter_ex]').val('');
				$('#editor input[name="campaign_days[]"]').each(function (index, val) { $(val).prop('checked', false) });
				$('#campaign-day-all').prop('checked', false);
				$('#editor input[name=campaign_time_start]').val('');
				$('#editor input[name=campaign_time_end]').val('');
				$('#editor input[name=campaign_date_start]').val('');
				$('#editor input[name=campaign_date_end]').val('');
				$('#editor input[name=campaign_centre_lat]').val('');
				$('#editor input[name=campaign_centre_lng]').val('');
				$('#editor input[name=campaign_radius]').val('');
				
				$('#editor input[name=campaign_id]').val('0');
				
				//map coordinates, set defaults
				map.panTo(new google.maps.LatLng(coords_default.lat, coords_default.lng));
				
				centre_marker.setVisible(false);
				
				$('#campaign_preview_banner_container').hide();
				
				wizardFirst();
				
				formValidator.resetForm();
				
				$('#campaign-editor').modal('toggle');
				  
			});
			
			$('#campaign-table tbody').on('click', 'tr.campaign i.icon-pencil', function() {
				  
				json = $.parseJSON($(this).closest('tr').attr('json'));
				
				$('#editor input[name=campaign_name]').val(json.name);
				$('#editor select[name=campaign_category] option[value="' + json.category + '"]').prop("selected", "selected");
				$('#editor textarea[name=campaign_text]').val(json.text); $('#campaign_preview_text').html(json.text);
				$('#editor input[name=campaign_response_text]').val(json.response_text);
				$('#campaign_response_text').keyup();
				if (json.banner.length > 0) {
					$('#campaign_preview_banner').attr('src', 'data:' + json.banner_type + ';base64,' + json.banner);
					$('#campaign_preview_banner_container').show();
				} else {
					$('#campaign_preview_banner').attr('src', '/img/banner_placeholder.png');
					$('#campaign_preview_banner_container').hide();
				}
				$('#editor input[name=campaign_filter]').val(json.filter); $('#campaign_preview_filter').html(json.filter);
				$('#editor input[name=campaign_filter_ex]').val(json.filter_ex);
				$('#editor input[name="campaign_days[]"]').each(function (index, val) { $(val).prop('checked', false) });
				$('#editor input[name="campaign_days[]"]').each(function (index, val) {
					var daysArr = json.weekdays.split(','), $el = $(val), idx;
					for (idx in daysArr) {
						if (daysArr[idx] == $el.val()) {
							$el.prop('checked', true); break;
						}
					}
					if (daysArr.length == 7) {
						$('#campaign-day-all').prop('checked', true);
					} else {
						$('#campaign-day-all').prop('checked', false);
					}
				});
				$('#editor input[name=campaign_time_start]').val(json.start_time.substring(0, json.start_time.length - 3));
				$('#editor input[name=campaign_time_end]').val(json.end_time.substring(0, json.end_time.length - 3));
				$('#editor input[name=campaign_date_start]').val(json.start_date);
				$('#editor input[name=campaign_date_end]').val(json.end_date);
				$('#editor input[name=campaign_centre_lat]').val(parseFloat(json.centre_lat).toFixed(7));
				$('#editor input[name=campaign_centre_lng]').val(parseFloat(json.centre_lng).toFixed(7));
				$('#editor input[name=campaign_radius]').val(json.radius);
				
				$('#editor input[name=campaign_id]').val(json.id);
				
				//map coordinates
				coords = { lat: parseFloat(json.centre_lat).toFixed(7), lng: parseFloat(json.centre_lng).toFixed(7) };
				map.panTo(new google.maps.LatLng(coords.lat, coords.lng));
				
				centre_marker.setVisible(false);
				centre_marker.setPosition(new google.maps.LatLng(coords.lat, coords.lng));
				centre_marker.setVisible(true);
				
				wizardFirst();
				
				$("form#editor").valid();
				
				$('#campaign-editor').modal('toggle');
				
			});
			
			var map_preview_styles = {};
			var map_opheme_styles = {};
			
			window.addEventListener("orientationchange", function() { location.reload(); }, false);
			
			function save_map_styles(type, id) {
				eval(type + '.width = $("' + id + '").css("width");');
				eval(type + '.height = $("' + id + '").css("height");');
				eval(type + '.position = $("' + id + '").css("position");');
				eval(type + '.top = $("' + id + '").css("top");');
				eval(type + '.zindex = $("' + id + '").css("z-index");');
			}
			
			function reset_map_styles(type, id) {
				eval('$("' + id + '").css("width", ' + type + '.width);');
				eval('$("' + id + '").css("height", ' + type + '.height);');
				if (id !== '.gm-style > :first-child') {
					eval('$("' + id + '").css("position", ' + type + '.position);');
					eval('$("' + id + '").css("top", ' + type + '.top);');
					eval('$("' + id + '").css("z-index", ' + type + '.zindex);');
				}
			}
			
			function map_cc_grow(maps_json) {
				
				var zoom;
				
				set_map_styles("#campaign-preview");
				set_map_styles(".campaign-map")
				set_map_styles(".opheme_map_");
				if (!(screen.width >= 768 && screen.width <= 1024)) {
					set_map_styles(".gm-style");
					set_map_styles(".gm-style > :first-child");
				}
				
				jQuery.each(maps_json, function(i, map) {
					//console.log(i + " / " + map.handle + " / " + map.id);
					set_map_styles("#map_campaign_" + map.id);
					google.maps.event.trigger(map.handle, "resize");
					eval("map_campaign_" + map.id + ".panTo(new google.maps.LatLng(coords_campaign_" + map.id + ".lat, coords_campaign_" + map.id + ".lng))");
					eval("zoom = map_campaign_" + map.id + ".getZoom()");
					setTimeout('eval("map_campaign_' + map.id + '.setZoom(' + (zoom + 1) + ')")', 500);
					setTimeout('eval("map_campaign_' + map.id + '.setZoom(' + (zoom) + ')")', 750);
				});
				
				$("#map_cc a .icon-resize-full").hide();
				$("#map_cc a .icon-resize-small").show();
				
			}
			
			function map_cc_shrink(maps_json) {
				
				var zoom;
				
				reset_map_styles("map_preview_styles", "#campaign-preview");
				reset_map_styles("map_opheme_styles", ".campaign-map");
				reset_map_styles("map_opheme_styles", ".opheme_map_");
				if (!(screen.width >= 768 && screen.width <= 1024)) {
					reset_map_styles("map_opheme_styles", ".gm-style");
					reset_map_styles("map_opheme_styles", ".gm-style > :first-child");
				}
				
				jQuery.each(maps_json, function(i, map) {
					//console.log(i + " / " + map.handle + " / " + map.id);
					reset_map_styles("map_opheme_styles", "#map_campaign_" + map.id);
					google.maps.event.trigger(map.handle, 'resize');
					eval("map_campaign_" + map.id + ".panTo(new google.maps.LatLng(coords_campaign_" + map.id + ".lat, coords_campaign_" + map.id + ".lng))");
					eval("zoom = map_campaign_" + map.id + ".getZoom()");
					setTimeout('eval("map_campaign_' + map.id + '.setZoom(' + (zoom + 1) + ')")', 500);
					setTimeout('eval("map_campaign_' + map.id + '.setZoom(' + (zoom) + ')")', 750);
				});
				
				$("#map_cc a .icon-resize-full").show();
				$("#map_cc a .icon-resize-small").hide();
				
			}
			
			function map_cc_zoomIn(maps_json) {
				var zoom;
				jQuery.each(maps_json, function(i, map) {
					eval("zoom = map_campaign_" + map.id + ".getZoom() + 1");
					eval("map_campaign_" + map.id + ".setZoom(" + zoom + ")");
				});
			}
			
			function map_cc_zoomOut(maps_json) {
				var zoom;
				jQuery.each(maps_json, function(i, map) {
					eval("zoom = map_campaign_" + map.id + ".getZoom() - 1");
					eval("map_campaign_" + map.id + ".setZoom(" + zoom + ")");
				});
			}
			
			function map_cc_close(maps_json) {
				jQuery.each(maps_json, function(i, map) {
					$("#map_campaign_" + map.id).fadeOut(300);
				});
				map_cc_shrink(maps_json);
				$("#map_cc").fadeOut(300);
				$(".preview-placeholder.icon").fadeIn(300);
				
				if (screen.width >= 320 && screen.width <= 568) $("#campaign-preview").hide();
			}
			
			//custom map controls
			var map_cc = $('<div id="map_cc"></div>');
			var maps_container = $('#campaign-preview');
			map_cc.append('<a onclick="map_cc_grow(maps_json)"><i class="icon-resize-full icon-large"></i></a>');
			map_cc.append('<a onclick="map_cc_shrink(maps_json)"><i class="icon-resize-small icon-large"></i></a>');
			map_cc.append('&nbsp;<a onclick="map_cc_zoomIn(maps_json)"><i class="icon-zoom-in icon-large"></i></a>');
			map_cc.append('&nbsp;<a onclick="map_cc_zoomOut(maps_json)"><i class="icon-zoom-out icon-large"></i></a>');
			map_cc.append('&nbsp;<a onclick="map_cc_close(maps_json)"><i class="icon-remove icon-large"></i></a>');
			maps_container.append(map_cc);
			
			save_map_styles("map_preview_styles", "#campaign-preview");
			save_map_styles("map_opheme_styles", ".campaign-map .opheme_map_");
		
		</script>