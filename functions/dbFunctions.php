<?php

// Database Manipulation Functions
// All of these functions are depreciated and should no longer be used
// as they will most likey be removed in future releases.

function contentsOfTableColumn($table,$column,$idColumn,$id) {
	global $targetDatabase;

	if(validateInput($id) && $column && $table) {
		connect();
		$result = mysql_db_query($targetDatabase,"select $column from $table where $idColumn=$id");

		if (!$result) { error(); return NULL; }
		else {
			$row = mysql_num_rows($result);
			
			if ($row == 1) {
				$row = mysql_fetch_array($result);
				
				return $row[$column];
			}
			else { return NULL; }
		}
		disconnect();
	}
	else { return NULL; }
}

function uniqueElementExists($element,$id,$table) {
	global $targetDatabase;

	if(validateInput($id) && $element && $table) {
		connect();	
		$result = mysql_db_query($targetDatabase,"select $element from $table where $element=$id");

		if (!$result) { error(); return false; }
		else {
			$row = mysql_num_rows($result);
			return ($row == 1) ? true : false;
		}
		disconnect();
	}
	else { return false; }
}

function dbTouch($element,$id,$table) {
	global $targetDatabase;

	if(validateInput($id) && $element && $table) {
		connect();	
		$result = mysql_db_query($targetDatabase,"UPDATE $table SET modificationDate = null WHERE $element=$id");

		if (!$result) { error(); return false; }
		else { return true;	}
		disconnect();
	}
	else { return false; }
}

// LDAP Additions

function LDAPSearch($searchKey) {
	GLOBAL $ldapServer,$ldapPersonSearchBase,$ldapCubeSearchBase;

	$LDIF =	`/usr/bin/ldapsearch -LLL -x -H '$ldapServer' -b '$ldapPersonSearchBase' $searchKey`;
	
	return $LDIF;
}

function parseLDIF($LDIF) {
	$returnedArray = array();
	
	if ($LDIF) {
	
		$ldifArray = split("\n", $LDIF);
			
		foreach ($ldifArray as $ldifLine) {	
			if (substr_count($ldifLine, ":: ") == 1) {
				list($myKey,$myValue) = split(":: ", $ldifLine);
				$returnedArray[$myKey] = $myValue;
			}
			else if (substr($ldifLine,0,1) == " ") { $returnedArray[$myKey] = $returnedArray[$myKey] . trim($ldifLine); }
			else if (substr_count($ldifLine,": ") == 1) {
				list($myKey,$myValue) = split(": ", $ldifLine);
				$returnedArray[$myKey] = $myValue;
			}
		}
		
		return $returnedArray;
	}
	else { return null; }
}

?>