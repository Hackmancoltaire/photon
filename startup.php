<?php

// - PHOTON Initialization file
// This file sets up the intial state for PHOTON. Any custom configuration should be done in
// the siteConfig.php file. ! Do not modify this file unless you know what you are doing !

	// - Execution Time Variable. This sets up a variable to determine how long it took to execute
	// the entire request. Use this variable in your navigation file or your methods to determine
	// how long each portion takes.
	list($usec, $sec) = explode(" ", microtime());
	$GLOBALS["phpStartTime"] = ((float)$usec + (float)$sec);
	
	// - Determine PHOTON location. It's possible to include your siteConfig file in lower levels
	// of your site. These routines determine exactly where the PHOTON folder is in the hierarchy
	// so that including the object classes can be done.
	$currentURL = parse_url($_SERVER['PHP_SELF']);
	$pathInfo = pathinfo($currentURL['path']);
	
	$dirStructure = explode("/",$pathInfo['dirname']);
	$dirStructureCount = count($dirStructure);
		
	for ($i=1; $i < $dirStructureCount; $i++) {
		$appRoot = "/" . $dirStructure[$i];

		if (file_exists($_SERVER['DOCUMENT_ROOT'] . $appRoot . "/PHOTON")) { $i = $dirStructureCount; }
	}

	// Declearing certain roots that you can use in your functions. For example if your site is at
	// http://www.photon.com but your document root for the site is actually /Library/WebServer/Documents
	// and the PHOTON directory is in the top level then $appRoot would be null, $appServerRoot would
	// be "/Library/WebServer/Documents" and $hostRoot would be "http://www.photon.com"
	$GLOBALS["appRoot"] = $appRoot;
	$GLOBALS["appServerRoot"] = $_SERVER['DOCUMENT_ROOT'] . $appRoot;
	$GLOBALS["hostRoot"] = "http://" . $_SERVER['SERVER_NAME'];
	
	// Create holder arrays for registered items and adaptors
	$GLOBALS["registeredLocations"] = array();
	$GLOBALS["registeredDatabases"] = array();
	
	// Now that we have the location of our PHOTON folder we should include the startup functions
	require_once $GLOBALS["appServerRoot"] . "/PHOTON/functions/startupFunctions.php";
	
	// The startupFunctions document contains all the items we need to register locations and adaptors.
	// Here we are registering the location for images that come standard with PHOTON. The location we
	// are registering is the file system location, not the web server location. Registering the location
	// lets you easily add more locations and still use one call to retrieve the image.
	phRegisterLocation("Images","PHOTON/images");

	// Array initialization is done here. Any arrays that we plan on using in PHOTON that will be globally
	// available will be declared here. If you have your own global variables you want to define be sure
	// to define them in your siteConfig file.

	// Parsed XML File caching array
	$GLOBALS["parsedXMLFiles"] = array();
		
	// The object Master is merely a location for adaptors to store caches of objects already pulled from
	// the database. When an object is pulled it is saved for future reference during the page execution.
	// If an object is saved or modified a new copy is placed in the cache OR the object is invalidated.
	// If you are writing your own adaptor please program it to handle passing items into the object master
	// array, OR to at minimum respect the boolean we are declaring here.
	$GLOBALS["objectMasterArray"] = array();
		
	// Load all function documents and classes in PHOTON
	phRegisterLocation("Functions",$GLOBALS["appServerRoot"] . "/PHOTON/functions");
	phRegisterLocation("Model",$GLOBALS["appServerRoot"] . "/PHOTON/model");
	phRegisterLocation("Adaptors",$GLOBALS["appServerRoot"] . "/PHOTON/adaptors");
	phRegisterLocation("Control",$GLOBALS["appServerRoot"] . "/PHOTON/control");
	phRegisterLocation("View",$GLOBALS["appServerRoot"] . "/PHOTON/view");

?>
