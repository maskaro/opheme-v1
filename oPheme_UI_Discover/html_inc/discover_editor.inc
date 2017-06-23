		
		<div id="campaign-editor" class="modal hide fade" data-keyboard="false" data-backdrop="static">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 style="color: black">Discover Editor</h3>
			</div>
			<form id="editor" action="/discover-process" method="post" enctype="multipart/form-data">
				<div class="modal-body">
					<div class="row-fluid">
						<div class="span12 form-inline">
							<!--<div class="alert alert-info alert-block">
								<button type="button" class="close">&times;</button>
								<h4>Useful Tip!!</h4>
								In order to create an effective discover you need to fill in sections below. The more information you will provide the more effective your discover will be!
							</div>-->
							<label class="more-info-tooltip" for="discover-name" data-toggle="tooltip" data-placement="right" title="Give your discover a cool name">Discover Name</label>
							<input type="text" class="input-block-level" required="required" name="discover_name" value="" placeholder="discover name" />
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12 form-inline">
							<label class="more-info-tooltip" for="discover-filter" data-toggle="tooltip" data-placement="right" title="Give your discover a search term to watch out for">Enter a Search Term (one word or a phrase) - Optional</label>
							<input type="text" class="input-block-level" name="discover_filter" value="" placeholder="discover search term" />
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12 form-inline">
							<label class="more-info-tooltip" for="discover-filter-ex" data-toggle="tooltip" data-placement="right" title="Give your discover terms to base message exclusion on">Enter some Exclusion Terms (space separated) - Optional</label>
							<input type="text" class="input-block-level" name="discover_filter_ex" value="" placeholder="discover exclusion terms, space separated" />
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label>What coordinates would you like the Discover to originate at?</label>
							<div class="row-fluid">
								<div class="span6">
									<label for="centre_lat">Coordinates</label>
									<input type="text" name="discover_centre_lat" id="discover_centre_lat" placeholder="latitude - click map to set or type your own">
									<input type="text" name="discover_centre_lng" id="discover_centre_lng" placeholder="longitude - click map to set or type your own">
									<a href="#" id="click_get_client_coords" class="btn btn-primary" style="width: 195px">Current Coordinates</a>
								</div>
								<div class="span6">
									<label for="radius">Radius (miles) - between 0.1 and 10 miles</label>
									<input type="text" name="discover_radius" placeholder="radius in miles"/>
								</div>
							</div>
						</div>
					</div>
					<div width="100%" height="100%" id="map_preview"></div>
					<br />
					<div class="row-fluid">
						<div style="padding-left: 15px">
							<label id="constraintsActivator"><strong>Click here to toggle Discover time and date constraints</strong></label>
						</div>
					</div>
					<div id="toggleConstraints" style="display: none">
						<div class="row-fluid">
							<div class="span12 form-inline">
								<label>Which days of the week would you like the Discover to be active on?</label>
								<div class="checkbox-wrapper">
									<input type="checkbox" id="discover-day-all" name="discover_days_all" value="All" />
									<label class="checkbox" id="tickAllDays">Every Day</label>
								</div>
								<?php
									$days = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
									foreach($days as $day)
										echo '<div class="checkbox-wrapper">
												<input type="checkbox" id="discover-day-' . strtolower($day) . '" name="discover_days[]" value="' . $day . '">
												<label for="discover-day-' . strtolower($day) . '" class="checkbox">' . substr($day, 0, 3) . '</label>
											</div>' . PHP_EOL;
								?>
							</div>
						</div>
						<div class="row-fluid">
							<div class="span12">
								<label>What times would you like the Discover to run between?</label>
								<div class="row-fluid">
									<div class="span6">
										<label for="sct1">Start Discover Time:</label>
										<input type="text" value="" id="sct1" name="discover_time_start" placeholder="09:00">
									</div>
								  <div class="span6">
										<label for="ect2">End Discover Time:</label>
										<input type="text" value="" id="ect2" name="discover_time_end" placeholder="17:00">
								  </div>
							  </div>
							</div>
						</div>
						<div class="row-fluid">
							<div class="span12">
								<label>When would you like the Discover to start and end?</label>
								<div class="row-fluid">
									<div class="span6">
										<label for="scd1">Start Discover Date:</label>
										<input type="text" value="" id="scd1" name="discover_date_start" placeholder="click to set">
									</div>
									<div class="span6">
										<label for="ecd2">End Discover Date:</label>
										<input type="text" value="" id="ecd2" name="discover_date_end" placeholder="click to set">
									</div>
							  </div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<a id="close-modal" href="#" class="btn">Close</a>
					<input type="submit" class="btn btn-large btn-primary" value="Save changes" />
				</div>
				<input type="hidden" name="discover_id" value="0" />
			</form>
		</div>