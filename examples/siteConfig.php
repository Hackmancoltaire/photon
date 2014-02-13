<?php

// Include the PHOTON startup file. The startup file sets up our initialization functions and
// timing routines. Keep all your site specific function declarations and class definition files
// outside of the PHOTON directory. This way upgrading is an easy drag-and-drop operation.
require_once "./PHOTON/startup.php";

// - Site configuration file
// Use this file to define any local variables to your application and to setup the PHOTON adaptors

	// The name of your application. This string can be used througout your site or in page titles.
	// This string will also be used to distinguish logs and sessions.
	$GLOBALS["appName"] = "[REMIX: MERCURY]";

	// This boolean ties into the default session class. Any subclassing of the session class should
	// incude a hook for this variable. That way you can disable the entire site from one place.
	$GLOBALS["allowAuthentication"] = TRUE;
	
	// Register locations for Images, Model, View and Controller classes/functions. Supply a type and location to the
	// phRegisterLocation function. Supplying "Images" will register the location for use with the
	// phImage function. Supplying "Adaptor", "Model", "View" or "Controller" will register the locations
	// with the class tracker and include the classes in each folder.
	
	phRegisterLocation("Images","myImages");
	phRegisterLocation("Model","myModels");
	phRegisterLocation("View","myViews");
	phRegisterLocation("Controller","myControllers");
	
	// The object Master is merely a location for adaptors to store caches of objects already pulled from
	// the database. When an object is pulled it is saved for future reference during the page execution.
	// If an object is saved or modified a new copy is placed in the cache OR the object is invalidated.
	// If you are writing your own adaptor please program it to handle passing items into the object master
	// array, OR to at minimum respect the boolean we are declaring here.
	$GLOBALS["objectMasterEnabled"] = TRUE;
	
	// Register databases with PHOTON. Current available adaptors are MySQL, FileMaker8, LDAP.
	// The phRegisterDatabase function takes the following arguments connection name, adaptor name, host & port, username
	// and password. And other arguments should be at the end of the function in a keyed array like:
	// phRegisterDatabase("connectionName","MySQL4Adaptor","localhost:123","user","pass",array("constants" => "MYSQL_SSL_VER"));

	phRegisterDatabase("Mercury","MySQL4Adaptor","localhost","root","apple");
	phRegisterDatabase("MyLDAP","LDAPAdaptor","ldap.example.com",null,null,array("searchBase" => "ou=People,o=Example Company"));

	// DEBUG Mode. Change this to true If you would like PHOTON to display the debug layer on top
	// of your site. The debug mode will let you see just what was loaded, pulled, cached and handled
	// per page execution. This is all dependant on your navigation file however as it must call the
	// debug display function when it draws the bottom navigation.
	$GLOBALS["debugMode"] = FALSE;
	
	// - Application Specific Variables
	// It is recommended that you put any other application specific variables in the array below.
	$GLOBALS["applicationVariables"] = array(
		"moduleLocation" => "myModules"
	);

?>