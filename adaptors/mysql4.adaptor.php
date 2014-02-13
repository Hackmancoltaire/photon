<?php

class MySQL4Adaptor extends phObject {
	
	/*
	This is an adaptor class that allows PHOTON to its standard calls with any database.
	The adaptor needs to subclass the following methods:
		- connect
		- disconnect
		- error
		- initWithAlternateKey
		- saveToDB
		- removeFromDB
		- joinWithObjectInTable
		- splitFromObjectInTable
		- idsForObjectInTable

	Once your adaptor is properly created you can then create your own object classes by
	extending this object class.
	*/
	
	function connect() {
		// You must call this function before ANY database queries, because sub fuctions may attempt to close connection.
		// This function maintains a single active connection to the database.
		
		if ($this->connectionName != null) {
			if (array_key_exists($this->connectionName,$GLOBALS["registeredDatabases"])) {
				if (array_key_exists("link",$GLOBALS["registeredDatabases"][$this->connectionName])) {
					if (array_key_exists("connectionCount",$GLOBALS["registeredDatabases"][$this->connectionName])) {
						$GLOBALS["registeredDatabases"][$this->connectionName]["connectionCount"]++;
					}
					else { $GLOBALS["registeredDatabases"][$this->connectionName]["connectionCount"] = 1; }
				}
				else {
					// Place specific database connection routine here
					$GLOBALS["registeredDatabases"][$this->connectionName]["link"] = mysql_connect(
						$GLOBALS["registeredDatabases"][$this->connectionName]["host"],
						$GLOBALS["registeredDatabases"][$this->connectionName]["user"],
						$GLOBALS["registeredDatabases"][$this->connectionName]["password"]
					);
					
					if ($GLOBALS["registeredDatabases"][$this->connectionName]["link"]) {
						$serverVersion = mysql_get_server_info($GLOBALS["registeredDatabases"][$this->connectionName]["link"]);
						
						if ($serverVersion) {
							if (version_compare($serverVersion,"4.1.0") >= 0) {
								$GLOBALS["registeredDatabases"][$this->connectionName]["args"][">41"] = TRUE;
							}
							else { $GLOBALS["registeredDatabases"][$this->connectionName]["args"][">41"] = FALSE; }
						}
					}
				}
			}
			else { /* This is where I would log a phError to the console */ }
		}
	}
	
	function disconnect() {
		if ($this->connectionName != null) {
			if (array_key_exists($this->connectionName,$GLOBALS["registeredDatabases"])) {
				if (array_key_exists("connectionCount",$GLOBALS["registeredDatabases"][$this->connectionName])) {
					$GLOBALS["registeredDatabases"][$this->connectionName]["connectionCount"]--;
					
					if (array_key_exists("link",$GLOBALS["registeredDatabases"][$this->connectionName]) && $GLOBALS["registeredDatabases"][$this->connectionName]["connectionCount"] < 0) {
						mysql_close($GLOBALS["registeredDatabases"][$this->connectionName]["link"]);
						$GLOBALS["registeredDatabases"][$this->connectionName]["connectionCount"] = 0;
					}
				}
				else { /* This is where I would log a phError to the console */ }
			}
			else { /* This is where I would log a phError to the console */ }
		}
	}
	
	function error($extra = null) {
		print mysql_info($GLOBALS["registeredDatabases"][$this->connectionName]["link"]) . "--" . mysql_errno($GLOBALS["registeredDatabases"][$this->connectionName]["link"]) . ": " . mysql_error($GLOBALS["registeredDatabases"][$this->connectionName]["link"]) . (($extra != null) ? ": $extra":"") . "\n";
		$this->disconnect();
	}

	
	function initWithAlternateKey($key,$value) {
		// This function is intended for key/value pairs that are unique and will not return multiple records.
		// If multiple records are returned then the initialization will not succeed.
		
		$processKeys = TRUE;
		
		if ($key != NULL && $value != NULL) {
			// Get row from MySQL and fill in all variables
			$this->connect();

			$query = "select * from " . $this->tableName . " where " . $this->objectDBConnections[$key][0] . "=" . (($this->objectDBConnections[$key][1] == 1) ? $value:"\"$value\"");
			$result = mysql_db_query($this->database,$query);
			
			if (!$result) { $this->error($query); }
			else {
				if (mysql_num_rows($result) == 1) {
					$row = mysql_fetch_array($result);
					$id = $row[$this->objectDBConnections["id"][0]];
					
					if ($GLOBALS["objectMasterEnabled"]) {
						// Object Master is enabled. Check for local copy of object before processing the data.
						$objectMaster = new phObjectMaster;

						// If not restored then we need to process the object normally
						$restored = $objectMaster->restoreObjectWithID($this,$id);

						if (!$restored) { $processKeys = TRUE; }
					}
					else { $proecessKeys = TRUE; }
					
					if ($processKeys) {
						// Process objectDBConnections array to initialize the object.
						$objectKeys = array_keys($this->objectDBConnections);
						$objectJoinsLocation = array_search("objectJoins",$objectKeys);
						
						if (!($objectJoinsLocation === FALSE)) { unset($objectKeys[$objectJoinsLocation]); }
						
						foreach ($objectKeys as $key) {
							$testType = NULL;
							
							if ($this->objectDBConnections[$key][0] == NULL || $this->objectDBConnections[$key][1] == NULL) { exit; $this->error($query); }
							else { $testType = $this->objectDBConnections[$key][1]; }
							
							if (is_null($row[$this->objectDBConnections[$key][0]])) { $this->$key = NULL; } // Value from DB is NULL
							else if ($testType == 1) { $this->$key = $row[$this->objectDBConnections[$key][0]]; } // Numeric
							else if ($testType == 2) { $this->$key = utf8_decode(stripslashes($row[$this->objectDBConnections[$key][0]])); } // String
							else if ($testType == 3) {
								$mySQLDate = $row[$this->objectDBConnections[$key][0]];
								list($mySQLYear,$mySQLMonth,$mySQLDay) = explode("-",$mySQLDate);
								$this->$key = mktime(0,0,0,$mySQLMonth,$mySQLDay,$mySQLYear);
							} // Date
							else if ($testType == 4) {
								if ($GLOBALS["registeredDatabases"][$this->connectionName]["args"][">41"] == FALSE) {
									$this->$key = $row[$this->objectDBConnections[$key][0]]; // Old timestamps
								}
								else if ($GLOBALS["registeredDatabases"][$this->connectionName]["args"][">41"] == TRUE) {
									// New timestamps are like datetimes
									$mySQLDate = $row[$this->objectDBConnections[$key][0]];
									list($mySQLDate,$mySQLTime) = explode(" ",$mySQLDate);
									list($mySQLYear,$mySQLMonth,$mySQLDay) = explode("-",$mySQLDate);
									list($mySQLHour,$mySQLMinute,$mySQLSecond) = explode(":",$mySQLTime);
									
									$this->$key = mktime($mySQLHour,$mySQLMinute,$mySQLSecond,$mySQLMonth,$mySQLDay,$mySQLYear);
								}
								else { $this->$key = null; }
							} // Time stamp
							else if ($testType == 5) { $this->$key = unserialize($row[$this->objectDBConnections[$key][0]]); } // PHP Array
							else if ($testType == 6) { $this->$key = $row[$this->objectDBConnections[$key][0]]; } // MySQL Encrypted Password
							else if ($testType == 7) {
								$mySQLDate = $row[$this->objectDBConnections[$key][0]];
								list($mySQLDate,$mySQLTime) = explode(" ",$mySQLDate);
								list($mySQLYear,$mySQLMonth,$mySQLDay) = explode("-",$mySQLDate);
								list($mySQLHour,$mySQLMinute,$mySQLSecond) = explode(":",$mySQLTime);
								
								$this->$key = mktime($mySQLHour,$mySQLMinute,$mySQLSecond,$mySQLMonth,$mySQLDay,$mySQLYear);
							} // Date Time
							else if ($testType == 8) { $this->$key = $row[$this->objectDBConnections[$key][0]]; } // Binary
							else { } // Invalid column type defined in objectDBConnections
						}
	
						$this->valid = true;
						
						if ($GLOBALS["objectMasterEnabled"]) {
							// Object Master is enabled. Save object into object master cache and set this object as a pointer.
							$objectMaster = new phObjectMaster;
	
							// If not restored then we need to process the object normally
							$stored = $objectMaster->storeObject($this);
						}
					}
				}
			}
			
			$this->disconnect();
		}
	}
	
	function saveToDB() {
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
			$objectJoinsLocation = array_search("objectJoins",$objectKeys);
			if (!($objectJoinsLocation === FALSE)) { unset($objectKeys[$objectJoinsLocation]); }

			foreach ($objectKeys as $key) {
				$testType = NULL;
				
				if ((!$this->valid && $this->backup->valid && 
					($this->$key != $this->backup->$key || $this->$key !== $this->backup->$key)) || 
					(!$this->valid && is_null($this->backup))) {
					// Add to change
					//print "Adding $key to change list...";
	
					if ($this->objectDBConnections[$key][0] == NULL || $this->objectDBConnections[$key][1] == NULL) { exit; $this->error($action . " " . $location); }
					else { $testType = $this->objectDBConnections[$key][1]; }
					
					if (is_null($this->$key)) { $setArray[] = $this->objectDBConnections[$key][0] . " = NULL"; }
					else if ($testType == 1) { $setArray[] = $this->objectDBConnections[$key][0] . " = " . $this->$key; } // Numeric
					else if ($testType == 2) { $setArray[] = $this->objectDBConnections[$key][0] . ' = "' . addslashes(utf8_encode($this->$key)) . '"'; } // String
					else if ($testType == 3) {
						// Dates can be entered in simple english ways. Only unsupported is m-d-y.
						if (stripos($this->$key,"now") === false) {
							$intValue = strtotime($this->$key);
							$mySQLDate = date("Y-m-d",$intValue);
						}
						else { $mySQLDate = date("Y-m-d"); }
						
						$setArray[] = $this->objectDBConnections[$key][0] . ' = "' . $mySQLDate . '"';
					} // Date
					else if ($testType == 4) {
						if ($GLOBALS["registeredDatabases"][$this->connectionName]["args"][">41"] == FALSE) {
							 $setArray[] = $this->objectDBConnections[$key][0] . " = " . $this->$key; // Old timestamps
						}
						else if ($GLOBALS["registeredDatabases"][$this->connectionName]["args"][">41"] == TRUE) {
							// New timestamps are like datetimes
							// Dates can be entered in simple english ways. Only unsupported is m-d-y.
							if (stripos($this->$key,"now") === false) {
								$intValue = strtotime($this->$key);
								$mySQLDate = date("Y-m-d H:i:s",$intValue);
							}
							else { $mySQLDate = date("Y-m-d H:i:s"); }

							$setArray[] = $this->objectDBConnections[$key][0] . ' = "' . $mySQLDate . '"';
						}
					} // Time stamp
					else if ($testType == 5) { $setArray[] = $this->objectDBConnections[$key][0] . ' = "' . addSlashes(serialize($this->$key)) . '"'; } // PHP Data Array
					else if ($testType == 6) { $setArray[] = $this->objectDBConnections[$key][0] . ' = PASSWORD("' . $this->$key . '")'; } // MySQL Encrypted Password
					else if ($testType == 7) {
						// Dates can be entered in simple english ways. Only unsupported is m-d-y.
						if (stripos($this->$key,"now") === false) {
							$intValue = strtotime($this->$key);
							$mySQLDate = date("Y-m-d H:i:s",$intValue);
						}
						else { $mySQLDate = date("Y-m-d H:i:s"); }

						$setArray[] = $this->objectDBConnections[$key][0] . ' = "' . $mySQLDate . '"';
					} // Date Time
					else if ($testType == 8) { $setArray[] = $this->objectDBConnections[$key][0] . ' = "' . addslashes($this->$key) . '"'; } // Binary
					else { } // Invalid column type defined in objectDBConnections						
				}
			}
			
			if (count($setArray) > 0) {
				$this->connect();
				
				$queryLine = "$action $this->tableName SET " . implode($setArray,", ") . $location; // Set to a different variable for error purposes
				$result = mysql_db_query($this->database,$queryLine);
				
				if (!$result) {
					$this->error($queryLine); 
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
				
				$this->disconnect();
			}
		}
	}
	
	function removeFromDB() {		
		if ($this->valid) {
			$this->connect();
			
			$query = "DELETE FROM $this->tableName WHERE " . $this->objectDBConnections[id][0] . " = $this->id";
			$result = mysql_db_query($this->database,$query);
					
			if (!$result) { $this->error($query); }
			else { $this->valid = false; }
			
			$this->disconnect();
		}
	}
	
	function joinWithObjectInTable($object,$tableName) {		
		if ($this->valid && $object->valid && !is_null($tableName)) {
			$this->connect();
			$result = mysql_db_query($this->database,"INSERT INTO $tableName SET " . $this->objectDBConnections[id][0] . " = $this->id," . $object->objectDBConnections[id][0] . " = $object->id");
			
			if (!$result) { $this->error(); return FALSE; }
			else { return TRUE; }
			
			$this->disconnect();
		}
		else { return FALSE; }
	}
	
	function splitFromObjectInTable($object,$tableName) {		
		if ($this->valid && $object->valid && !is_null($tableName)) {
			$this->connect();
			$result = mysql_db_query($this->database,"DELETE FROM $tableName WHERE " . $this->objectDBConnections[id][0] . " = $this->id AND " . $object->objectDBConnections[id][0] . " = $object->id");
			
			if (!$result) { $this->error(); return FALSE; }
			else { return TRUE; }
			
			$this->disconnect();
		}
		else { return FALSE; }
	}
	
	function idsForObjectInTable($object,$tableName) {		
		$tempArray = array();
		
		if ($this->valid) {
			$this->connect();

			$query = "
				SELECT $tableName." . $object->objectDBConnections[id][0] . " FROM $tableName
				LEFT JOIN " . $object->tableName . " ON $tableName." . $object->objectDBConnections[id][0] . " = " . $object->tableName . "." . $object->objectDBConnections[id][0] . "
				WHERE " . $this->objectDBConnections[id][0] . " = $this->id";
			$result = mysql_db_query($this->database,$query);
			
			if (!$result) { $this->error($query); return NULL; }
			else {				
				while($row = mysql_fetch_array($result)) { $tempArray[] = $row[$object->objectDBConnections[id][0]]; }
								
				return $tempArray;
			}
			
			$this->disconnect();
		}
		else { return NULL; }
	}
	
	function query($query = null,$returnAsResource=TRUE) {
		$results = array();

		if ($query) {
			$this->connect();
			
			$result = mysql_db_query($this->database,$query);
		
			if (!$result) { $this->error($query); return NULL; }
			else {
				while ($row = mysql_fetch_array($result)) {
					$results[] = $row;
				}
				
				return $results;
			}
		
			$this->disconnect();
		}
		else { return NULL; }
	}
}

?>