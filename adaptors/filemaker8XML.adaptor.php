<?php

class FileMaker8XMLAdaptor extends phObject {
	
	/*
	This is an adaptor class that allows PHOTON to use or databases other then MySQL.
	The adaptor needs to subclass the following methods:
		- initWithAlternateKey
		- saveToDB
		- removeFromDB
		- joinWithObjectInTable
		- splitFromObjectInTable
		- idsForObjectInTable
		
	Once those methods are sublcassed correctly you will be able to set the default adaptor in the magic-dance configuartion file
	or set manually the connection that you want to be used when an object is created by using the setAdaptor() method and calling
	the class of the adaptor like '$myObject->setAdaptor("MySQL4.Adaptor");'.
	*/
	
	function initWithAlternateKey($key,$value) {
		// This function is intended for key/value pairs that are unique and will not return multiple records.
		// If multiple records are returned then the initialization will not succeed.
		
		GLOBAL $targetDatabase,$objectMasterEnabled;
		
		$processKeys = TRUE;
		
		if ($key != NULL && $value != NULL) {
			// Get row from MySQL and fill in all variables
			connect();
			$result = mysql_db_query($targetDatabase,"select * from $this->tableName where " . $this->objectDBConnections[$key][0] . "=\"$value\"");
			
			if (!$result) { error(); }
			else {
				if (mysql_num_rows($result) == 1) {
					$row = mysql_fetch_array($result);
					$id = $row[$this->objectDBConnections["id"][0]];
					
					if ($objectMasterEnabled) {
						// Object Master is enabled. Check for local copy of object before processing the data.

						if ($GLOBALS[objectMasterArray][$this->objectDBConnections["id"][0]][$row[$this->objectDBConnections["id"][0]]]) {
							if (!$GLOBALS[objectMasterArray][get_class($this)]) {
								$GLOBALS[objectMasterArray][get_class($this)] = get_class_vars(get_class($this));
							}
							
							foreach ($GLOBALS[objectMasterArray][get_class($this)] as $field) {
								$this->$field =& $GLOBALS[objectMasterArray][$this->objectDBConnections["id"][0]][$id]->$field;
							}
						}
						else { $processKeys = TRUE; }
					}
					else { $proecessKeys = TRUE; }
					
					if ($processKeys) {
						// Process objectDBConnections array to initialize the object.
						$objectKeys = array_keys($this->objectDBConnections);
						
						foreach ($objectKeys as $key) {
							$testType = NULL;
							
							if ($this->objectDBConnections[$key][0] == NULL || $this->objectDBConnections[$key][1] == NULL) { exit; error(); }
							else { $testType = $this->objectDBConnections[$key][1]; }
							
							if ($row[$this->objectDBConnections[$key][0]] == "") { /* MySQL equivilant of NULL */ }
							else if ($testType == 1) { $this->$key = $row[$this->objectDBConnections[$key][0]]; } // Numeric
							else if ($testType == 2) { $this->$key = stripslashes($row[$this->objectDBConnections[$key][0]]); } // String
							else if ($testType == 3) {
								$mySQLDate = $row[$this->objectDBConnections[$key][0]];
								list($mySQLYear,$mySQLMonth,$mySQLDay) = explode("-",$mySQLDate);
								$this->$key = mktime(0,0,0,$mySQLMonth,$mySQLDay,$mySQLYear);
							} // Date
							else if ($testType == 4) { $this->$key = $row[$this->objectDBConnections[$key][0]]; } // Time stamp
							else if ($testType == 5) { $this->$key = unserialize($row[$this->objectDBConnections[$key][0]]); } // PHP Array
							else if ($testType == 6) { $this->$key = $row[$this->objectDBConnections[$key][0]]; } // MySQL Encrypted Password
							else if ($testType == 7) { $this->$key = $row[$this->objectDBConnections[$key][0]]; } // Date Time
							else { } // Invalid column type defined in objectDBConnections
						}
	
						$this->valid = true;
						
						if ($objectMasterEnabled) {
							// Object Master is enabled. Save object into object master cache and set this object as a pointer.
							if (!$GLOBALS[objectMasterArray][get_class($this)]) {
								$GLOBALS[objectMasterArray][get_class($this)] = array_keys(get_class_vars(get_class($this)));
							}
							
							$GLOBALS[objectMasterArray][$this->objectDBConnections["id"][0]][$this->id] = $this;

							foreach ($GLOBALS[objectMasterArray][get_class($this)] as $field) {
								$GLOBALS[objectMasterArray][$this->objectDBConnections["id"][0]][$this->id]->$field =& $this->$field;
							}
						}
					}
				}
			}
			
			disconnect();
		}
	}
	
	function saveToDB() {
		global $targetDatabase;

		$action = NULL;
		$location = NULL;
		$setArray = array();
		
		if ($this->valid) { /* Do nothing! This object has not been modified. */ }
		else if (!$this->valid && $this->backup->valid) {
			$action = "UPDATE";
			$location = " WHERE " . $this->objectDBConnections[id][0] . " = $this->id";
		}
		else { $action = "INSERT INTO"; }
		
		if ($action != NULL) {
			// Compile sets
			// Process objectDBConnections array to save only the changed data
			$objectKeys = array_keys($this->objectDBConnections);
				
			foreach ($objectKeys as $key) {
				$testType = NULL;
				
				if ((!$this->valid && $this->backup->valid && $this->$key != $this->backup->$key) || 
					(!$this->valid && $this->backup == NULL) && ($this->$key == "null" || $this->$key == "" || $this->$key != "0" || is_null($this->$key))) {
					// Add to change
	
					if ($this->objectDBConnections[$key][0] == NULL || $this->objectDBConnections[$key][1] == NULL) { exit; error(); }
					else { $testType = $this->objectDBConnections[$key][1]; }
					
					if ($this->$key == "null" || $this->$key == "" || (is_null($this->$key) && $this->$key != "0")) { $setArray[] = $this->objectDBConnections[$key][0] . " = NULL"; }
					else if ($testType == 1) { $setArray[] = $this->objectDBConnections[$key][0] . " = " . $this->$key; } // Numeric
					else if ($testType == 2) { $setArray[] = $this->objectDBConnections[$key][0] . ' = "' . addslashes($this->$key) . '"'; } // String
					else if ($testType == 3) {
						// Dates can be entered in simple english ways. Only unsupported is m-d-y.
						$intValue = strtotime($this->$key);
						$mySQLDate = date("Y-m-d",$intValue);
						$setArray[] = $this->objectDBConnections[$key][0] . ' = "' . $mySQLDate . '"';
					} // Date
					else if ($testType == 4) { $setArray[] = $this->objectDBConnections[$key][0] . " = " . $this->$key; } // Time stamp
					else if ($testType == 5) { $setArray[] = $this->objectDBConnections[$key][0] . ' = "' . addSlashes(serialize($this->$key)) . '"'; } // PHP Data Array
					else if ($testType == 6) { $setArray[] = $this->objectDBConnections[$key][0] . ' = PASSWORD("' . $this->$key . '")'; } // MySQL Encrypted Password
					else if ($testType == 7) { $setArray[] = $this->objectDBConnections[$key][0] . ' = "' . $this->$key . '"'; } // Date Time
					else { } // Invalid column type defined in objectDBConnections						
				}
			}
			
			if (count($setArray) > 0) {
				connect();
				
				$queryLine = "$action $this->tableName SET " . implode($setArray,", ") . $location; // Set to a different variable for error purposes
				$result = mysql_db_query($targetDatabase,$queryLine);
				
				if (!$result) {
					error(); 
					//print $queryLine;
					$this->valid = false;
				}
				else {
					//print_r($setArray); print $queryLine;
					if (!$this->valid && $this->backup->valid) {
						$this->valid = TRUE;
						$this->backup = NULL;
					}
					else {
						$this->id = mysql_insert_id();
						$this->valid = TRUE;
					}
				}
				
				disconnect();
			}
		}
	}
	
	function removeFromDB() {
		global $targetDatabase;
		
		if ($this->valid) {
			connect();
			$result = mysql_db_query($targetDatabase,"DELETE FROM $this->tableName WHERE " . $this->objectDBConnections[id][0] . " = $this->id");
					
			if (!$result) { error(); }
			else { $this->valid = false; }
			
			disconnect();
		}
	}
	
	function joinWithObjectInTable($object,$tableName) {
		global $targetDatabase;
		
		if ($this->valid && $object->valid && !is_null($tableName)) {
			connect();
			$result = mysql_db_query($targetDatabase,"INSERT INTO $tableName SET " . $this->objectDBConnections[id][0] . " = $this->id," . $object->objectDBConnections[id][0] . " = $object->id");
			
			if (!$result) { error(); return FALSE; }
			else { return TRUE; }
			
			disconnect();
		}
		else { return FALSE; }
	}
	
	function splitFromObjectInTable($object,$tableName) {
		global $targetDatabase;
		
		if ($this->valid && $object->valid && !is_null($tableName)) {
			connect();
			$result = mysql_db_query($targetDatabase,"DELETE FROM $tableName WHERE " . $this->objectDBConnections[id][0] . " = $this->id AND " . $object->objectDBConnections[id][0] . " = $object->id");
			
			if (!$result) { error(); return FALSE; }
			else { return TRUE; }
			
			disconnect();
		}
		else { return FALSE; }
	}
	
	function idsForObjectInTable($object,$tableName) {
		global $targetDatabase;
		
		$tempArray = array();
		
		if ($this->valid) {
			connect();
			
			$result = mysql_db_query($targetDatabase,"
				SELECT $tableName.* FROM $tableName
				LEFT JOIN " . $object->tableName . " ON $tableName." . $object->objectDBConnections[id][0] . " = " . $object->tableName . "." . $object->objectDBConnections[id][0] . "
				WHERE " . $this->objectDBConnections[id][0] . " = $this->id");
			
			if (!$result) { error(); return NULL; }
			else {				
				while($row = mysql_fetch_array($result)) { $tempArray[] = $row[$object->objectDBConnections[id][0]]; }
								
				return $tempArray;
			}
			
			disconnect();
		}
		else { return NULL; }
	}
}

?>