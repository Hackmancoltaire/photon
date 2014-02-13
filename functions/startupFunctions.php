<?php

// - PHOTON Startup Functions. This file is intended to be used during the
// initial startup of the execution. These functions manage the location
// of the registered images, functions, adapators, etc.

// Require system function file to be sure we have the proper function to
// register locations.
require_once $GLOBALS["appServerRoot"] . "/PHOTON/functions/fsFunctions.php";

// phRegosterLocation - Stores locations in the registeredLocations array
// and includes files from thos directories. All files are included unless
// the type is "Images" or the filename contains "_DISABLED".

function phRegisterLocation($locationType,$location) {
	if (!array_key_exists($locationType,$GLOBALS["registeredLocations"])) {
		$GLOBALS["registeredLocations"][$locationType] = array($location);
	}
	else { array_push($GLOBALS["registeredLocations"][$locationType], $location); }

	if ($locationType != "Images") {
		$files = directoryListing($location);
		
		foreach ($files as $file) {
			if (strpos($file,".php") && (strpos($file,"_DISABLED") == FALSE)) {
				require_once $location . "/" . $file;
			}
		}
	}
}

function phRegisterDatabase($name,$adaptor,$host,$user,$password,$args = null) {
	if (!array_key_exists($name,$GLOBALS["registeredDatabases"])) {
		$GLOBALS["registeredDatabases"][$name] = array(
			"adaptor"	=> $adaptor,
			"host"		=> $host,
			"user"		=> $user,
			"password"	=> $password,
			"args"		=> $args
		);
	}
	else { /* This is where I would spit an error to the console */ }
}

?>