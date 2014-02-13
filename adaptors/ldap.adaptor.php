<?php

class LDAPAdaptor extends phObject {
	
	/*
	This adaptor is currently only a shell intended for queries. Later it will be
	upgraded to handle saving items to the database just like any other object.
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
					// Plase specific database connection routine here
					if (strpos(":",$GLOBALS["registeredDatabases"][$this->connectionName]["host"])) {
						list($host,$port) = explode(":",$GLOBALS["registeredDatabases"][$this->connectionName]["host"]);

						$GLOBALS["registeredDatabases"][$this->connectionName]["link"] = ldap_connect($host,$port);
					}
					else {
						$GLOBALS["registeredDatabases"][$this->connectionName]["link"] = ldap_connect(
							$GLOBALS["registeredDatabases"][$this->connectionName]["host"]
						);
					}
					
					if ($GLOBALS["registeredDatabases"][$this->connectionName]["user"] == null) {
						$connectionStatus = ldap_bind($GLOBALS["registeredDatabases"][$this->connectionName]["link"]);
					}
					else {
						$connectionStatus = ldap_bind(
							$GLOBALS["registeredDatabases"][$this->connectionName]["link"],
							$GLOBALS["registeredDatabases"][$this->connectionName]["user"],
							$GLOBALS["registeredDatabases"][$this->connectionName]["password"]
						);
					}
					
					if (!$connectionStatus) { /* This is where I would log an phError to the console. */ }
					
					// Temporary hack
					return $connectionStatus;
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
						ldap_close($GLOBALS["registeredDatabases"][$this->connectionName]["link"]);
						$GLOBALS["registeredDatabases"][$this->connectionName]["connectionCount"] = 0;
					}
				}
				else { /* This is where I would log a phError to the console */ }
			}
			else { /* This is where I would log a phError to the console */ }
		}	
	}
	
	function error($extra) {
		$this->valid = false;
		
		print ldap_errno($GLOBALS["registeredDatabases"][$this->connectionName]["link"]) . ": " . ldap_error($GLOBALS["registeredDatabases"][$this->connectionName]["link"]) . (($extra != null) ? ": $extra":"") . "\n";
		$this->disconnect();
	}

	function initWithAlternateKey($key,$value) {
		// This function is intended for key/value pairs that are unique and will not return multiple records.
		// If multiple records are returned then the initialization will not succeed.
		
		$processKeys = TRUE;
		
		if ($key != NULL && $value != NULL) {
			// Get row from database and fill in all variables
			if (array_key_exists("args",$GLOBALS["registeredDatabases"][$this->connectionName])) {
				if (array_key_exists("searchBase",$GLOBALS["registeredDatabases"][$this->connectionName]["args"])) {
					$this->connect();
					
					$result = ldap_search(
						$GLOBALS["registeredDatabases"][$this->connectionName]["link"],
						$GLOBALS["registeredDatabases"][$this->connectionName]["args"]["searchBase"],
						$this->objectDBConnections[$key][0] . "=$value",
						$this->dbConnectionKeys()
					);
				}
				else { /* This is where an phError would appear in the console */ }
			}
			else { /* This is where an phError would appear in the console */ }
			
			if (!$result) { $this->error(); }
			else {
				if (ldap_count_entries($GLOBALS["registeredDatabases"][$this->connectionName]["link"],$result) == 1) {
					$row = ldap_get_entries($GLOBALS["registeredDatabases"][$this->connectionName]["link"],$result);
					$entry = ldap_first_entry($GLOBALS["registeredDatabases"][$this->connectionName]["link"],$result);

					$row = $row[0];
					$id = $row[$this->objectDBConnections["id"][0]][0];
					
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
						
						foreach ($objectKeys as $key) {
							$testType = NULL;
							$dbCK = strtolower($this->objectDBConnections[$key][0]);
							
							if ($dbCK == NULL || $this->objectDBConnections[$key][1] == NULL) { exit; $this->error(); }
							else { $testType = $this->objectDBConnections[$key][1]; }
							
							if ($row[$dbCK][0] == "") { /* MySQL equivilant of NULL */ }
							else if ($testType == 1) { $this->$key = $this->processLDAPValues($row[$dbCK]); } // Numeric
							else if ($testType == 2) { $this->$key = $this->processLDAPValues($row[$dbCK]); } // String
							else if ($testType == 3 || $testType == 4 || $testType == 7) { $this->$key = strtotime($row[$dbCK][0]); } // Date / Date TIme / Timestamp
							else if ($testType == 5) { $this->$key = unserialize($row[strtolower($this->objectDBConnections[$key][0])][0]); } // PHP Array
							else if ($testType == 6) { $this->$key = $this->processLDAPValues($row[$dbCK]); } // MySQL Encrypted Password
							else if ($testType == 8) { $binaryValues = ldap_get_values_len($GLOBALS["registeredDatabases"][$this->connectionName]["link"],$entry,strtolower($dbCK)); $this->$key = $binaryValues[0]; } // Binary Data
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
	
		if ($this->valid) { /* Do nothing! This object has not been modified. */ }
		else if (!$this->valid && $this->backup->valid) { $action = "modify"; }
		else { $action = "add"; }
		
		if ($action != NULL) {
			// Compile sets
			// Process objectDBConnections array to save only the changed data
			$objectKeys = array_keys($this->objectDBConnections);
			$objectJoinsLocation = array_search("objectJoins",$objectKeys);
			if (!($objectJoinsLocation === FALSE)) { unset($objectKeys[$objectJoinsLocation]); }

			foreach ($objectKeys as $key) {
  				$testType = NULL;
				$dbCK = strtolower($this->objectDBConnections[$key][0]);
				
				if ((!$this->valid && $this->backup->valid && 
					($this->$key != $this->backup->$key || $this->$key !== $this->backup->$key)) || 
					(!$this->valid && is_null($this->backup))) {
					// Add to change
					// print "Adding $key to change list...";
	
					if ($dbCK == NULL || $this->objectDBConnections[$key][1] == NULL) { exit; $this->error($action . " " . $this->table); }
					else { $testType = $this->objectDBConnections[$key][1]; }
					
					if (is_null($this->$key)) { /* Skip this atm */ }
					else if ($testType == 1) { $setArray[$dbCK] = $this->$key; } // Numeric
					else if ($testType == 2) { $setArray[$dbCK] = $this->$key; } // String
					else if ($testType == 3 || $testType == 4 || $testType == 7) {
						// Dates can be entered in simple english ways. Only unsupported is m-d-y.
						if (stripos(strtolower($this->$key),"now") === false) { $setArray[$dbCK] = date("YmdHisT",$this->$key); }
						else { $setArray[$dbCK] = date("YmdHisT"); }
					} // Date / Date Time / Timestamp
					else if ($testType == 5) { $setArray[$dbCK] = serialize($this->$key); } // PHP Data Array
					else if ($testType == 6) { $setArray[$dbCK] = $this->$key; } // MySQL Encrypted Password
					else if ($testType == 8) { $setArray[$dbCK] = $this->$key; } // Binary
					else { } // Invalid column type defined in objectDBConnections						
				}
			}
			
			if (count(array_keys($setArray)) > 0) {
				$this->connect();
				
				// Pop the DN off the
				
				if ($action == "add") {
					$dn = $setArray["dn"];
					unset($setArray["dn"]);
				
					$result = ldap_add($GLOBALS["registeredDatabases"][$this->connectionName]["link"],$dn,$setArray);
				}
				else if ($action == "modify") {
					$result = ldap_mod_replace($GLOBALS["registeredDatabases"][$this->connectionName]["link"],$this->id,$setArray);
				}
				else {
					// No valid action provided.
					$this->error("No valid action provided to LDAP Adaptor.");
				}

				if (!$result) {
					$this->error("DN: " . $this->id . $dn . " Set Array: " . print_r($setArray,true));
					$this->valid = false;
				}
				else {
					if (!$this->valid && $this->backup->valid) {
						$this->valid = TRUE;
						$this->backup = NULL;
					}
					else { $this->valid = TRUE; }
				}
				
				$this->disconnect();
			}
		}
	}

	function removeFromDB() {
		if ($this->valid) {
			$this->connect();
			
			if ($this->id) {
				$result = ldap_delete($GLOBALS["registeredDatabases"][$this->connectionName]["link"],$this->id);
				
				if (!$result) { $this->error(); }
				else { $this->valid = false; }
				
				$this->disconnect();
			}
		}
	}
	
	function processLDAPValues($input) {
		if (!is_array($input)) { return $input; }
		else {
			if ($input["count"] == 1) { return $input[0]; }
			else { return array_slice($input,1); }
		}
	}
}