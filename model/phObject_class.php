<?php

class phObject {
	
	// Database variables
	
		var $valid;				// Don't mess with this value unless you KNOW what you are doing.
		var $connectionName;	// The name of the connection that should be used.
		var $tableName;
	
	// Object necessities
	
		var $backup;
		var $phSearchRank;		// Used by phSearch class. Do not declare OR set this manually...unless you know what your doing.
		var $phLink; 			// Used by the phSearchTable to manage links for multiple objects in a table.
		var $dbConnectionKeysCache;
		
	// Describe the connection to the database
	// The reason I'm doing this is because the object should know how to save itself even more then I do.
	// Meaning it should know to correctly manage strings and dates. So we describe how it connects with the DB.
	// Then the object pulls the table description and gets types for each data point so it knows how to save the
	// data later. Is this going overboard?
	// 1 = Numeric
	// 2 = String
	// 3 = Date
	// 4 = Time Stamp
	// 5 = PHP Array: This is a serialized piece of data from PHP
	// 6 = Encrypted MySQL Password
	// 7 = Date Time
	// 8 = Binary Data
	// IMPORTANT: At least one item is necessary to copy over data and that is the "id" key. It must be attached to
	// a MySQL attribute.
	/*
	var $objectDBConnections = array(
		id 				=> array("collectionID",1),
		partNumber		=> array("partNumber",2),
		partName		=> array("partName",2),
		partState		=> array("partState",2),
		supercedes		=> array("supercedes",2),
		supercededBy	=> array("supercededBy",2),
		gmDate			=> array("gmDate",4),
		rfa				=> array("rfa",2)
	);
	*/
	
	function alloc() {
		// This function sets up initial values for all objects.
	
		$this->valid = false;
		$this->dbConnectionKeysCache = array();
	}
	
	// Methods
	
	// NEW ??
// 	
// 		function setValueForKey($value,$key) {
// 		//print "$key = " . getType($this->$key) . " Value = " . getType($value);
// 		if ($key != "id") {
// 			if (is_null($value) && is_null($this->$key)) {
// 				/* Do nothing. Same values */ 
// 				//print "Doing nothing 1. $value - " . $this->$key;
// 			}
// 			else if ((is_null($value) && $value != "0") && (is_null($this->$key) && $this->$key != "0")) {
// 				/* Do more nothing */
// 				//print "Doing nothing 2. $value - " . $this->$key;
// 			}
// 			else if ($this->$key === $value && $this->key == $value) {
// 				/* No update is needed */
// 				//print "Doing nothing 3. $value - " . $this->$key;
// 			}
// 			else if (is_null($this->$key) && $value == "") {
// 				// More nothing
// 				//print "Doing nothing because '' is equal to NULL for us.";
// 			}
// 			else if ($this->$key == $value) {
// 				//print "More nothing. Values are equal";
// 			}
// 			else if ($this->objectDBConnections[$key][1] == 3 && $this->$key == strtotime($value)) { /* Do more nothing */ }
// 			else {
// 				//print "Setting object invalid because '$value' is not equal to '" . $this->$key . "'.";
// 				
// 				if ($this->valid && $this->backup == NULL) { $this->backup(); } // We are modifying the object, backup object first. Object is now invalid.
// 				
// 				if (is_null($value)) {
// 					//print "Setting $key to PHP NULL 1.";
// 					$this->$key = NULL;
// 				}
// 				else {
// 					//print "First Setting $key to '$value'";
// 					$this->$key = $value;
// 				}
// 			}
// 		}
// 		else {
// 			if (($value == ("null" || "")) && is_null($value) && $value != "0") {
// 				//print "Setting $key to PHP NULL 2.";
// 				$this->$key = NULL;
// 			}
// 			else {
// 				//print "Second Setting $key to '$value'";
// 				$this->$key = $value;
// 			}
// 		}
// 	}

	
	function setValueForKey($value,$key) {
		//print "$key = " . getType($this->$key) . " Value = " . getType($value);
		if ($this->valid && $key != "id") {
			if (is_null($value) && is_null($this->$key)) {
				/* Do nothing. Same values */ 
				//print "Doing nothing 1. $value - " . $this->$key;
			}
			else if ((is_null($value) && $value != "0") && (is_null($this->$key) && $this->$key != "0")) {
			/* Do more nothing */
				//print "Doing nothing 2. $value - " . $this->$key;

			}
			else if ($this->$key === $value && $this->key == $value) {
				/* No update is needed */
				//print "Doing nothing 3. $value - " . $this->$key;

			}
			else if ($this->objectDBConnections[$key][1] == 3 && $this->$key == strtotime($value)) { /* Do more nothing */ }
			else {
				//print "Setting object invalid because '$value' is not equal to '" . $this->$key . "'.";
				
				if ($this->backup == NULL) { $this->backup(); } // We are modifying the object, backup object first. Object is now invalid.
				
				if (is_null($value)) {
					// print "Setting $key to PHP NULL 1.";
					$this->$key = NULL;
				}
				else {
					// print "Setting $key to '$value'";
					$this->$key = $value;
				}
			}
		}
		else {
			if (($value == ("null" || "")) && is_null($value) && $value != "0") {
				 //print "Setting $key to PHP NULL 2.";
				$this->$key = NULL;
			}
			else {
				 //print "Setting $key to '$value'";
				$this->$key = $value;
			}
		}
	}
	
	function backup() {
		$this->backup = unserialize(serialize($this));
		$this->valid = FALSE;
	}
	
	function revertToBackup() {
		/* Disabled in PHP 5 */
		//$this = $this->backup;
		//$this->backup = NULL;
	}

	function initWithID($id) {		
		if ($GLOBALS["objectMasterEnabled"]) {
			// Object Master is enabled. Check with object master cache for object before pulling.
			$objectMaster = new phObjectMaster;

			// If not restored then we need to process the object normally
			$restored = $objectMaster->restoreObjectWithID($this,$id);

			if (!$restored) {
				if (validateInput($id)) { $this->initWithAlternateKey("id",$id); }
			}
		}
		else if (validateInput($id)) { $this->initWithAlternateKey("id",$id); }
	}
	
	function dbConnectionKeys() {
		// This function will give you all the keys expected from the database in an array.
	
		if ($this->dbConnectionKeysCache == null) {
			$objectKeys = array_keys($this->objectDBConnections);
			
			if (!(array_search("objectJoins",$objectKeys) === FALSE)) { unset($objectKeys["objectJoins"]); }
			
			foreach($objectKeys as $key) {
				array_push($this->dbConnectionKeysCache,$this->objectDBConnections[$key][0]);
			}
			
			return $this->dbConnectionKeysCache;
		}
		else { return $this->dbConnectionKeysCache; }
	}
}

?>