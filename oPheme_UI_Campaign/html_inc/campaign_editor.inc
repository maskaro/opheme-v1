		
		<div id="campaign-editor" class="modal hide fade" data-keyboard="false" data-backdrop="static" style="min-height: 0px">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 style="color: black">Campaign Editor Wizard</h3>
			</div>
			<form id="editor" action="/campaign-process" method="post" enctype="multipart/form-data">
				<div class="modal-body">
					<div id="wizard_info">
						<div class="row-fluid">
							<div class="span12 form-inline">
								<!--<div class="alert alert-info alert-block">
									<button type="button" class="close">&times;</button>
									<h4>Useful Tip!!</h4>
									In order to create an effective campaign you need to fill in sections below. The more information you will provide the more effective your campaign will be!
								</div>-->
								<label class="more-info-tooltip" for="campaign-name" data-toggle="tooltip" data-placement="right" title="Give your campaign a cool name">Campaign Name</label>
								<input type="text" class="input-block-level" required="required" name="campaign_name" value="" placeholder="campaign name" />
							</div>
						</div>
						<div class="row-fluid">
							<div class="span12 form-inline">
								<label>Assign your Campaign a Category</label>
								<div class="controls">
									<select name="campaign_category" class="input-block-level">
										<?php
											$cats = array("----------", "Bars", "Clubs", "Restaurants", "Clothing", "Music", "General Shopping");
											foreach($cats as $cat)
												echo '<option value="' . $cat . '">' . $cat . '</option>' . PHP_EOL;
										?>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div id="wizard_filters">
						<div class="row-fluid">
							<div class="span12 form-inline">
								<label class="more-info-tooltip" for="campaign-filter" data-toggle="tooltip" data-placement="right" title="Give your campaign one word or a phrase to look for">Enter a Search Term (one word or a phrase) - Optional</label>
								<input type="text" class="input-block-level" id="campaign_filter" name="campaign_filter" value="" placeholder="campaign search term" />
							</div>
						</div>
						<div class="row-fluid">
							<div class="span12 form-inline">
								<label class="more-info-tooltip" for="campaign-filter-ex" data-toggle="tooltip" data-placement="right" title="Give your campaign keywords to base message exclusion on">Enter some Exclusion Terms (space separated) - Optional</label>
								<input type="text" class="input-block-level" name="campaign_filter_ex" value="" placeholder="campaign exclusion terms, space separated" />
							</div>
						</div>
					</div>
					<div id="wizard_user">
						<div class="row-fluid">
							<div class="span12 form-inline">
								<label class="more-info-tooltip" for="campaign-response-text" data-toggle="tooltip" data-placement="right" title="This is the Tweet your recipients will receive">Campaign Tweet - Characters left: <span id="campaign_response_text_count">55</span></label>
								<input type="text" class="input-block-level" required="required" id="campaign_response_text" name="campaign_response_text" value="" placeholder="campaign response (10-55 characters)" />
								<div style="font-size: 12px">
									Your Tweet should include the recipient name, your organisation name and the recipient name.
									Use <span id="campaign_response_text_percent_r">%r to indicate where the recipient name will appear</span> and
									<span id="campaign_response_text_percent_c">%c to indicate where the organisation name will appear</span>.
									For example: <strong>Hi %r, here at %c we've got some special deals tonight!</strong>
								</div>
							</div>
						</div>
						<div class="row-fluid">
							<div class="span12 form-inline">
								<label class="more-info-tooltip" for="campaign-response-preview" data-toggle="tooltip" data-placement="right" title="Campaign response message preview">Campaign Response Preview</label>
								<div class="row-fluid" style="border-style: solid; border-width: 1px;">
									<div class="span12">
										<div id="campaign-container">
											<h4 id="campaign_response_preview">(no response message entered yet)</h4>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="wizard_campaign">
						<div class="row-fluid">
							<div class="span12 form-inline">
								<label class="more-info-tooltip" for="campaign-banner" data-toggle="tooltip" data-placement="right" title="Give your campaign a meaningful banner">Campaign Banner (Allowed image types: PNG, JPG/JPEG)</label>
								<input type="file" accept="image/*" class="input-block-level" id="campaign_banner" name="campaign_banner" />
							</div>
						</div>
						<div class="row-fluid">
							<div class="span12 form-inline">
								<label class="more-info-tooltip" for="campaign-text" data-toggle="tooltip" data-placement="right" title="Give your campaign a meaningful text">Campaign Message (min. 5 characters, can use HTML tags)</label>
								<textarea rows="4" class="input-block-level" required="required" id="campaign_text" name="campaign_text" value="" placeholder="campaign text (min 5 characters)"></textarea>
							</div>
						</div>
						<div class="row-fluid">
							<div class="span12 form-inline">
								<label class="more-info-tooltip" for="campaign-preview" data-toggle="tooltip" data-placement="right" title="Campaign message overall preview">Campaign Preview</label>
								<div class="row-fluid" style="border-style: solid; border-width: 1px; font-size: 75%; margin-left: auto; margin-right: auto">
									<div class="span12">
										<div id="campaign-container">
											<h4 style="font-size: inherit">Hey there, <strong id="campaign_preview_username">@Username</strong>!<br />You have received a message from <strong><a><?php echo $_SESSION['user']['business_type']; ?></a></strong>.</h4>
											<!--<div class="row-fluid">
												<div class="span12" style="padding-top: 5px; padding-bottom: 0px;">
													<label for="tweet-text" style="font-size: inherit">Your tweet that triggered this message</label>
													<span class="input-block-level">This is a tweet that matched <strong id="campaign_preview_filter">(no search term set yet, all will be matched)</strong> search term.</span>
												</div>
											</div>-->
											<div class="row-fluid">
												<div class="span12" style="padding-top: 5px; padding-bottom: 0px;">
													<label for="campaign-text" style="font-size: inherit">Message from <strong><a><?php echo $_SESSION['user']['business_type']; ?></a></strong></label>
													<span class="input-block-level" id="campaign_preview_text">(no message set yet) <a>http://ophe.me/52381b3dd474b6.32985547</a></span>
												</div>
											</div>
											<div class="row-fluid" id="campaign_preview_banner_container" style="display: none">
												<div class="span12" style="padding-top: 5px; padding-bottom: 0px;">
													<label for="campaign-banner" style="font-size: inherit">Campaign Banner</label>
													<span class="input-block-level"><img src='/img/banner_placeholder.png' title='Banner' id="campaign_preview_banner" style="max-width: 485px" /></span>
												</div>
											</div>
											<div class="row-fluid">
												<div class="span12" style="padding-top: 5px; padding-bottom: 0px;">
													<label for="tweet-created-at" style="font-size: inherit">Date of tweet</label>
													<span class="input-block-level"><?php echo date("D M j G:i:s T Y"); ?></span>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="wizard_running">
						<div class="row-fluid">
							<div class="span12 form-inline">
								<label>Which days of the week would you like the campaign to be active on?</label>
								<div class="checkbox-wrapper">
									<input type="checkbox" id="campaign-day-all" name="campaign_days_all" value="All" />
									<label class="checkbox" id="tickAllDays">Every Day</label>
								</div>
								<?php
									$days = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
									foreach($days as $day)
										echo '<div class="checkbox-wrapper">
												<input type="checkbox" id="campaign-day-' . strtolower($day) . '" name="campaign_days[]" value="' . $day . '" />
												<label for="campaign-day-' . strtolower($day) . '" class="checkbox">' . substr($day, 0, 3) . '</label>
											</div>' . PHP_EOL;
								?>
							</div>
						</div>
						<div class="row-fluid">
							<div class="span12">
								<label>What times would you like the Campaign to run between?</label>
								<div class="row-fluid">
									<div class="span6">
										<label for="sct1">Start Campaign Time:</label>
										<input type="text" value="" id="sct1" name="campaign_time_start" placeholder="09:00">
									</div>
								  <div class="span6">
										<label for="ect2">End Campaign Time:</label>
										<input type="text" value="" id="ect2" name="campaign_time_end" placeholder="17:00">
								  </div>
							  </div>
							</div>
						</div>
						<div class="row-fluid">
							<div class="span12">
								<label>When would you like the Campaign to start and end?</label>
								<div class="row-fluid">
									<div class="span6">
										<label for="scd1">Start Campaign Date:</label>
										<input type="text" value="" id="scd1" name="campaign_date_start" placeholder="click to set">
									</div>
									<div class="span6">
										<label for="ecd2">End Campaign Date:</label>
										<input type="text" value="" id="ecd2" name="campaign_date_end" placeholder="click to set">
									</div>
							  </div>
							</div>
						</div>
					</div>
					<div id="wizard_location">
						<div class="row-fluid">
							<div class="span12">
								<label>What coordinates would you like the Campaign to originate at?</label>
								<div class="row-fluid">
									<div class="span6">
										<label for="centre_lat">Coordinates</label>
										<input type="text" name="campaign_centre_lat" id="campaign_centre_lat" placeholder="latitude - click map to set or type your own">
										<input type="text" name="campaign_centre_lng" id="campaign_centre_lng" placeholder="longitude - click map to set or type your own">
										<a href="#" id="click_get_client_coords" class="btn btn-primary" style="width: 195px">Current Coordinates</a>
									</div>
									<div class="span6">
										<label for="radius">Radius (miles) - between 0.1 and 1 miles</label>
										<input type="text" name="campaign_radius" placeholder="radius in miles"/>
									</div>
								</div>
							</div>
						</div>
						<div width="100%" height="100%" id="map_preview"></div>
					</div>
				</div>
				<div class="modal-footer" style="display: none">
					<a id="close-modal" href="#" class="btn">Close</a>
					<input type="submit" class="btn btn-large btn-primary" value="Save changes" />
				</div>
				<input type="hidden" name="campaign_id" value="0" />
			</form>
		</div>