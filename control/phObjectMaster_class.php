<?php

class phObjectMaster {
	// This class works with the adaptor classes to manage the storage and retreival
	// of objects from the various databases. The object master has no parent class
	// and does not talk to the databases directly, but merely manages the
	// global object master array.

	function phObjectMaster() {
	
	}

	function restoreObjectWithID($object,$id) {
		$objectClass = get_class($object);
	
		if ($GLOBALS["objectMasterArray"][$objectClass]["ids"][$id]) {
			// Object exists. Now all we need to do is set the vars of said object to what is in the cache
			
			if (!$GLOBALS["objectMasterArray"][$objectClass]["keys"]) {
				$GLOBALS["objectMasterArray"][$objectClass]["keys"] = array_keys(get_class_vars($objectClass));
			}
			
			foreach ($GLOBALS["objectMasterArray"][$objectClass]["keys"] as $field) {
				$object->$field =& $GLOBALS["objectMasterArray"][$objectClass]["ids"][$id]->$field;
			}
			
			return true;
		}
		else { return false; }
	}
	
	function storeObject($object) {
		$objectClass = get_class($object);

		if (is_null($GLOBALS["objectMasterArray"][$objectClass]["keys"])) {
			$GLOBALS["objectMasterArray"][$objectClass]["keys"] = array_keys(get_class_vars($objectClass));
		}
							
		$GLOBALS["objectMasterArray"][$objectClass]["ids"][$object->id] = $object;

		foreach ($GLOBALS["objectMasterArray"][$objectClass]["keys"] as $field) {
			$GLOBALS["objectMasterArray"][$objectClass]["ids"][$object->id]->$field =& $object->$field;
		}
		
		return true;
	}
}

?>