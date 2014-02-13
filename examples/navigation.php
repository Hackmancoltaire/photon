<?php

// - PHOTON Navigation Template. This file is meant to be a starting point for your site's look and feel.
// Any JavaScript and CSS declarations should be made in non PHOTON locations. Modify the following functions
// if you want to pass in data to change the look of the navigation based on outside parameters. Remember
// that the drawTopNavigation function should be run after authentication is performed.

function drawTopNavigation() {
	global $appRoot;

	print "<meta http-equiv='content-type' content='text/html; charset=utf-8'>
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"ttp://www.w3.org/TR/html4/loose.dtd\">
<html>
<head>
	<link rel='stylesheet' href='$appRoot/PHOTON/PHOTON.css'>
</head>
<script type='text/javascript' language='Javascript' src='$appRoot/PHOTON/PHOTON.js'></script>
<body>";

}

function drawBottomNavigation() {
	global $phpStartTime, $debugMode;

	$execTime = getmicrotime() - $phpStartTime;

	print "
</body>
<!-- Execution Time: $execTime seconds -->
</html>";

	if ($debugMode) { displayDebugConsole(); }
}

?>