<?php header("Location: http://campaign.opheme.com/"); die("Under maintenance... Redirecting to Campaign module... "); ?>
<!DOCTYPE html>
<html>
  <head>
    <title>o:Pheme</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="css/bootstrap.css" rel="stylesheet" media="screen">
    <link href="css/responsive.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet" media="screen">
  </head>
  <body>
    <!-- Sticky Navigation -->
    <div id="navContainer" class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <!-- .btn-navbar is used as the toggle for collapsed navbar content -->
          <a id="mainNavToggle" class="btn btn-navbar" data-toggle="collapse" data-target="#mainNav">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <!-- Be sure to leave the brand out there if you want it shown -->
          <a id="ophemeBrand" class="brand" href="/">o<span class="orange">:P</span>heme</a>
          <a class="brand" id="doit" data-toggle="collapse" data-target="#filter"><i class="icon-caret-down icon-large"></i></a>
          <!-- Everything you want hidden at 940px or less, place within here -->
          <div id="mainNav" class="nav-collapse collapse">
            <ul class="nav pull-right">
              <!--<li class="divider-vertical"></li>
              <li><a href="index.html">Home</a></li>
              <li class="divider-vertical"></li>
              <li><a href="#">About</a></li>
              <li class="divider-vertical"></li>
              <li><a href="#">FAQ</a></li>-->
              <li><a href="#jobs_div" role="button" data-toggle="modal">Jobs</a></li>
              <li class="divider-vertical"></li>
              <li><a href="#instructions" role="button" data-toggle="modal">Help</a></li>
            </ul>
            <!-- .nav, .navbar-search, .navbar-form, etc -->
          </div>
        </div>
      </div>
    </div>
    <!-- change COLLAPSE IN to COLLAPSE to get filtering to collapse -->
    <div id="filter" class="container collapse in" >
      <div class="navbar">
        <div class="navbar-inner">
          <div class="container">
            <!-- Be sure to leave the brand out there if you want it shown -->
            <a class="brand" >Filtering:</a>
            <!-- Everything you want hidden at 940px or less, place within here -->
            <div id="filterNav" class="row-fluid">
              <form id="job_form" class="form-inline">
                <div class="span3 row">
                  <label for="centre_lat" class="span2"><i id="getClientCoords" data-html="true" title="Coordinates (Click here to<br />get your current coordinates)" class="icon-map-marker icon-large" data-placement="bottom"></i> </label>
                  <input type="number" name="centre_lat" placeholder="latitude" id="centre_lat" class="span5">
                  <input type="number" name="centre_lng" placeholder="longtitude" id="centre_lng" class="span5">
                </div>
                <div class="span1 row">
                  <label id="radiusLabel" for="radius" class="span1"><i title="Radius in Miles" class="icon-screenshot icon-large" data-placement="bottom"></i> </label>
                  <input type="number" name="radius" value="0.1" step="0.01" class="span11" />
                </div>
                <div id="filterInput" class="span6 row">
                  <label for="filter" class="span1"><i title="Filter" class="icon-filter icon-large" data-placement="bottom"></i> </label>
                  <textarea name="filter" rows="1" maxlength="140" class="span10"></textarea>
                </div>
                <div id="submitButton" class="span1 row">
                  <input class="btn btn-small span12" type="submit" value="Go!">
                </div>
        				<input type="hidden" name="initiating_user_email" value="email@me.com" />
        				<input type="hidden" name="source" value="TWITTERGEO" />
        				<input type="hidden" name="map_type" value="gmaps" />
              </form>
              <!-- .nav, .navbar-search, .navbar-form, etc -->
            </div>
          </div>
        </div>
      </div>
    </div>
    <div id="instructions" class="modal hide fade in" tabindex="-1" role="dialog" aria-labelledby="instructions" aria-hidden="true" data-backdrop="true">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
        <h3 id="myModalLabel">Help</h3>
      </div>
      <div class="modal-body">
        <h4>Getting Started</h4>
        <p>
          <img class="img-polaroid" src="img/coordinates.png" />
        </p>
        <p>
          1. Click on the map to get coordinates or type in coordinates.
        </p>
        <p>
          <img class="img-polaroid" src="img/radius.png" />
        </p>
        <p>
          2. Choose a radius of an area that you want to see.
        </p>
        <p>
          <img class="img-polaroid" src="img/filter.png" />
        </p>
        <p>
          (optional)<br />
          3. You can filter out tweets to include only those containing specific words, for example: free #coffee<br />
          (minimum three characters and separated by space " " like in the example above)
        </p>
        <p>
          <img class="img-polaroid" src="img/go.png" />
        </p>
        <p>
          4. Click Go! button.
        </p>
      </div>
      <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
      </div>
    </div>
    <div id="jobs_div" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="jobs" aria-hidden="true" data-backdrop="true">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
        <h3>Jobs</h3>
      </div>
      <div class="modal-body" id="job_listings"></div>
    </div>
    <div id="map_canvas"></div>
    <div id="landingModal"><div class="hero-unit"></div></div>
    <!-- EXTERNAL FRAMEWORKS -->
    <script type="text/javascript" src="includes/jquery-1.9.1.min.js"></script>
    <script type="text/javascript" src="includes/jquery.validate-1.11.0.min.js"></script>
    <script type="text/javascript" src="includes/jquery.validate.additional-methods.min.js"></script>
    <script type="text/javascript" src="includes/bootstrap.min.js"></script>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD0ODP93_CT5Bpjb2R8SignzeyrQ9tU8_8&sensor=false"></script>
    <script type="text/javascript" src="includes/tooltip.js"></script>
    <script type="text/javascript" src="includes/markerclusterer_compiled.js"></script>
	<!-- OWN FRAMEWORKS -->
    <script type="text/javascript" src="js/landing_page.js"></script>
    <script type="text/javascript" src="js/oPheme_ui-0.1.js"></script>
    <!-- PRODUCTION CODE -->
    <script type="text/javascript" src="js/production.js"></script>
    <script type="text/javascript" src="includes/ga.js"></script>
  </body>
</html>