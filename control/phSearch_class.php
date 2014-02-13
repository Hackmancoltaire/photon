<?php

// PHOTON Search Class
// At this time phSearch can only use the MySQL adaptor to save searches.
// Possibly this will change into saving searches into the session.
// IMPORTANT: Also at this time the search class can only search MySQL databases.
//		this will change as soon as I can extrapolate the search terms from the db.

class phSearch extends MySQL4Adaptor {

	var $objectDBConnections = array(
		"id"						=> array("phSearchID",1),
		"searchTerms"				=> array("searchTerms",2),
		"searchTermCount"			=> array("searchTermCount",1),
		"searchAbsolutes"			=> array("searchAbsolutes",5),
		"searchAbsoluteCount"		=> array("searchAbsoluteCount",1),
		"targets"					=> array("targets",5)
	);
	
	var $id;
	var $criteria;				// This would be criteria applied to ALL objects.
	var $tables;
	
	// searchMode - Can be single or multi. This is internally set based on joining. Multi mode takes
	// each target object class and performs global seaerch on them. Single will take the targets in
	// reverse order and build a single query based on joining parameters.
	
	var $searchMode;			
	
	var $targets;				// Objects and Filter criteria.
	var $results;				// Object type result and rank of result.
	var $resultCount;

	function phSearch($id=NULL) {
		$this->tableName = "saved_searches";
		$this->valid = false;
		$this->searchMode = "multi"; // Can be multi or single
		$this->tables = array();
		
		if ($id) { $this->initWithID($id); }
		else { $this->id = uniqid("phSearch-"); }
	}
	
	function addTargetObjectWithFilter($object,$filter=NULL) {
		// $object is a class with an objectDBConnections array.
		// $filter is an array of keys that match keys of the objectDBConnections array.
		
		$className = get_class($object);
		
		if (!$this->targets[$className]) {
			// If there is no target object already in the target array, add it!
			
			$this->targets[$className] = array(
				"object"	=>	$object,
				"filter"	=>	$filter,
				"joins"		=> array(),
				"criteria"	=> array()
			);
		}
	}
	
	function joinTargetWithTarget($targetA=null,$targetB=null) {
		if (!is_null($targetA) && !is_null($targetB)) {
			$targetClassName = get_class($targetA);
		
			if ($this->targets[$targetClassName]) {
				$this->targets[$targetClassName]["joins"][] = $targetB;
				
				if ($this->searchMode == "multi") { $this->searchMode = "single"; }
			}
		}
	}
	
	function setSearchCriteria($criteria) { $this->setSearchCriteriaForTarget($criteria); }
	
	function setSearchCriteriaForTarget($criteria,$target=null) {
		if (!is_null($target)) {
			$targetClassName = get_class($target);
		
			if (!is_null($this->targets[$targetClassName])) {
				$this->targets[$targetClassName]["criteria"] = $criteria;
			}
		}
		else {
			if (is_array($criteria) && !is_null($criteria)) { $this->criteria = $criteria; }
		}
	}
	
	function queryForTargetClass($className,&$tableArray) {		
		$searchCriteria = array();
		$absoluteCriteria = array();
		$tables = array();
		$joinClauses = array();
		$queryString = null;
		$finalSQL = "";
		
		if (!is_null($this->targets[$className]["criteria"]) && !is_null($this->criteria)) {
			$this->setSearchCriteriaForTarget($this->criteria,$this->targets[$className]["object"]);
		}

		// If we have any absolutes set in the search criteria we need to set them up in the query.
		if (!is_null($this->targets[$className]["criteria"]["absolutes"])) {
			$searchAbsoluteCount = count($this->targets[$className]["criteria"]["absolutes"]);
		}
		else { $searchAbsoluteCount = 0; }
		
		if ($searchAbsoluteCount > 0) {
			$searchAbsoluteKeys = array_keys($this->targets[$className]["criteria"]["absolutes"]);
			
			for ($absoluteI=0; $absoluteI < $searchAbsoluteCount; $absoluteI++) {
				$absoluteModifierValueCount = count($this->targets[$className]["criteria"]["absolutes"][$searchAbsoluteKeys[$absoluteI]]);

				if ($absoluteModifierValueCount % 2 == 0) {
					for ($aMVC=0; $aMVC < $absoluteModifierValueCount - 1; $aMVC += 2) {
						$absoluteCriteria[] = $this->targets[$className]["object"]->tableName . "." .
							$this->targets[$className]["object"]->objectDBConnections[$searchAbsoluteKeys[$absoluteI]][0] . 
							$this->targets[$className]["criteria"]["absolutes"][$searchAbsoluteKeys[$absoluteI]][$aMVC] . 
							(($this->targets[$className]["object"]->objectDBConnections[$searchAbsoluteKeys[$absoluteI]][1] == 2) ?
								('"' . $this->targets[$className]["criteria"]["absolutes"][$searchAbsoluteKeys[$absoluteI]][$aMVC+1] . '"'):
								$this->targets[$className]["criteria"]["absolutes"][$searchAbsoluteKeys[$absoluteI]][$aMVC+1]);
					}
				}
				else {
					// phError("$this - Absolute parameters are uneven.");
				}
			}
		}
		
		if (!is_null($this->targets[$className]["criteria"]["sorts"])) {
			$sortKeys = array_keys($this->targets[$className]["criteria"]["sorts"]);
			
			if (!is_null($sortKeys)) {
				$searchSorts = $this->targets[$className]["criteria"]["sorts"];
				$searchSortCount = count($sortKeys);
			}
		}
		else { $searchSortCount = 0; }

		// Now depending on if we have search terms set. We setup our search parameters
		if (!is_null($this->targets[$className]["criteria"]["terms"])) {
			$searchTerms = $this->seperateTerms($this->targets[$className]["criteria"]["terms"]);
			
			// Check to see if we actually have something to work on.
			$searchKeyCount = count($searchKeys);

			for ($keyI=0; $keyI < $searchKeyCount; $keyI++) {
				$currentKey = $searchKeys[$keyI];
				
				if (!is_Null($currentKey)) {	
					$searchCriteria[] = $this->produceSearchTermSQL($this->targets[$className]["object"]->objectDBConnections[$currentKey][0],$searchTerms);
				}
			}
		}
		
		if (!is_null($this->targets[$className]["joins"])) {
			$joinCount = count($this->targets[$className]["joins"]);
			$joinClause = array();

			for ($joinI=0; $joinI < $joinCount; $joinI++) {
				$foundJoin = false;

				$joinClassName = get_class($this->targets[$className]["joins"][$joinI]);
				
				if (!is_null($this->targets[$className]["object"]->objectDBConnections["objectJoins"])) {
					if (!is_null($this->targets[$className]["object"]->objectDBConnections["objectJoins"][$joinClassName])) {
						$joinTargetTable = $this->targets[$className]["joins"][$joinI]->tableName;
						$joinTable = $this->targets[$className]["object"]->objectDBConnections["objectJoins"][$joinClassName];
						$joinClause[] = 						
							// (contact_partner_join.contactID = contact.contactID)
							"(" . $joinTable . "." . $this->targets[$className]["object"]->objectDBConnections["id"][0] . " = " . $this->targets[$className]["object"]->tableName . "." . $this->targets[$className]["object"]->objectDBConnections["id"][0] . ")" .

							// AND (contact_partner_join.partnerID = partner
							" AND (" . $joinTable . "." . $this->targets[$className]["joins"][$joinI]->objectDBConnections["id"][0] . " = " . $joinTargetTable . "." . $this->targets[$className]["joins"][$joinI]->objectDBConnections["id"][0] . ")";
						
						$tableArray[] = $this->targets[$className]["object"]->tableName;
						$tableArray[] = $joinTable;
						$tableArray[] = $joinTargetTable;

						$foundJoin = true;
					}
				}
				
				if (!$foundJoin) {
					if (!is_null($this->targets[$className]["joins"][$joinI]->objectDBConnections["objectJoins"])) {
						if (!is_null($this->targets[$className]["joins"][$joinI]->objectDBConnections["objectJoins"][$className])) {
							$joinTargetTable = $this->targets[$className]["joins"][$joinI]->tableName;
							$joinTable = $this->targets[$className]["joins"][$joinI]->objectDBConnections["objectJoins"][$className];
							$joinClause[] .= 						
								// (contact_partner_join.contactID = contact.contactID)
								"(" . $joinTable . "." . $this->targets[$className]["object"]->objectDBConnections["id"][0] . " = " . $this->targets[$className]["object"]->tableName . "." . $this->targets[$className]["object"]->objectDBConnections["id"][0] . ")" .
	
								// AND (contact_partner_join.partnerID = partner
								" AND (" . $joinTable . "." . $this->targets[$className]["joins"][$joinI]->objectDBConnections["id"][0] . " = " . $joinTargetTable . "." . $this->targets[$className]["joins"][$joinI]->objectDBConnections["id"][0] . ")";
						
							$tableArray[] = $this->targets[$className]["object"]->tableName;
							$tableArray[] = $joinTable;
							$tableArray[] = $joinTargetTable;

							$foundJoin = true;
						}
					}
				}
			}
		}
		
		$searchTermCount = count($searchTerms);
			
		if ($searchTermCount > 0) { $queryString .= implode(" OR ",$searchCriteria); }

		// Add the absolute criteria onto the end of the queryString.				
		if (count($absoluteCriteria) > 0) { $queryString .= (($searchTermCount > 0) ? " AND ":"") . implode(" AND ",$absoluteCriteria); }
		
		// Include sorting method
		if ($searchSortCount > 0) {
			$sortKeys = array_keys($searchSorts);
		
			$queryString .= " ORDER BY ";
			
			for ($sortI=0; $sortI < $searchSortCount; $sortI++) {
				$queryString .= $sortKeys[$sortI] . " " . $searchSorts[$sortKeys[$sortI]];
				
				if ($sortI != $searchSortCount - 1) { $queryString .= ", "; }
			}
		}	

		if (count($joinClause) > 0) {
			// Add Search Terms
			if (is_null($queryString)) { $finalSQL = "(" . implode(") AND (",$joinClause) . ")"; }
			else { $finalSQL .= "(" . implode(") AND (",$joinClause) . ") AND " . $queryString; }
		}
		else { $finalSQL .= $queryString; }
		
		return $finalSQL;
	}
	
	function search($criteria = NULL) {
		if ($criteria) { $this->setSearchCriteria($criteria); }
	
		$classes = array_keys($this->targets);
		$classesCount = count($classes);
		$tableArray = array();
		$queries = array();
		$queryString = null;
		
		if ($this->searchMode == "single") { 
			// Do a single query with the joined search queries.

			for ($objectI=0; $objectI < $classesCount; $objectI++) {
				// Reset for a new object
				$currentClass = $classes[$objectI];
	
				$queries[] = $this->queryForTargetClass($currentClass,$tableArray);
			}
	
			if (count($queries) > 0) {
			
				$queryString = "SELECT " . (($classesCount > 1) ? ("DISTINCT " . $this->targets[$classes[0]]["object"]->tableName . ".*"):"*") . " FROM " . implode(",",array_unique($tableArray)) . " WHERE " . implode(" AND ",$queries);
	
				// Uncomment here to see the query string.
				//print "Single: " . $queryString . "\n";
				
				$result = $this->targets[$classes[0]]["object"]->query($queryString);
				
				if ($result === null) { $this->targets[$currentClass]["object"]->error(); }
				else {
					$searchTerms = $this->seperateTerms($this->targets[$currentClass]["criteria"]["terms"]);
					$searchTermCount = count($searchTerms);

					if ($this->targets[$currentClass]["filter"]) {
						// Object has filter. Use filter.
						$searchKeys = $this->targets[$currentClass]["filter"];
					}
					else {
						// Object has no filter. Use objectDBConnections keys as filter.
						$searchKeys = array_keys($this->targets[$currentClass]["object"]->objectDBConnections);						
						
						// If there is an "id" key we need to remove it so we don't search on it. So we unset it.
						$containsID = array_search("id",$searchKeys);
						if (!($containsID === FALSE)) { unset($searchKeys[$containsID]); }
					}
					
					// Check to see if we actually have something to work on.
					$searchKeyCount = count($searchKeys);
				
					while(current($result) !== FALSE) {
						$row = current($result);
						
						if (!$this->results[$classes[0]]["object"]) {
							$this->results[$classes[0]]["object"] = $this->targets[$classes[0]]["object"];
						}
						
						if ($searchTermCount > 0) {
							$occuranceCount = 0;
							
							for ($keyI=0; $keyI < $searchKeyCount; $keyI++) {
								$currentKey = $searchKeys[$keyI];
	
								$haystack = strtolower($row[$this->targets[$classes[0]]["object"]->objectDBConnections[$currentKey][0]]);
								
								for ($termI=0; $termI < $searchTermCount; $termI++) {
									$occuranceCount += substr_count($haystack,strtolower($this->targets[$classes[0]]["criteria"]["terms"][$termI]));
								}
							}
						}
						else { $occuranceCount = 0; }
	
						if (is_Null($this->results[$classes[0]]["ids"][$row[$this->targets[$classes[0]]["object"]->objectDBConnections["id"][0]]])) {
							$this->results[$classes[0]]["ids"][$row[$this->targets[$classes[0]]["object"]->objectDBConnections["id"][0]]] = $occuranceCount;
						}
						else { $this->results[$classes[0]]["ids"][$row[$this->targets[$classes[0]]["object"]->objectDBConnections["id"][0]]] += $occuranceCount; }

						next($result);
					}
				}
			}
		}
		else if ($this->searchMode == "multi") {
			// Do one query for each object class and pool results.
			
			for ($objectI=0; $objectI < $classesCount; $objectI++) {
				// Reset for a new object
				$currentClass = $classes[$objectI];
			
				$queryString = "SELECT " . (($classesCount > 1) ? "DISTINCT ":"") . "* FROM " . $this->targets[$currentClass]["object"]->tableName . " WHERE " . $this->queryForTargetClass($currentClass,$tableArray);
	
				// Uncomment here to see the query string.
				//print "Multi: " . $queryString . "\n";
				
				$result = $this->targets[$currentClass]["object"]->query($queryString);
				
				//print_r($result);
				
				if ($result === null) { $this->targets[$currentClass]["object"]->error($queryString); }
				else {
					$searchTerms = $this->seperateTerms($this->targets[$currentClass]["criteria"]["terms"]);
					$searchTermCount = count($searchTerms);

					if ($this->targets[$currentClass]["filter"]) {
						// Object has filter. Use filter.
						$searchKeys = $this->targets[$currentClass]["filter"];
					}
					else {
						// Object has no filter. Use objectDBConnections keys as filter.
						$searchKeys = (!(is_null($this->targets[$currentClass]["object"]->objectDBConnections))) ? array_keys($this->targets[$currentClass]["object"]->objectDBConnections):null;
						
						// If there is an "id" key we need to remove it so we don't search on it. So we unset it.
						$containsID = (!(is_null($searchKeys))) ? array_search("id",$searchKeys):FALSE;
						if (!($containsID === FALSE)) { unset($searchKeys[$containsID]); }
					}
					
					// Check to see if we actually have something to work on.
					$searchKeyCount = count($searchKeys);
				
					while(current($result) !== FALSE) {
						$row = current($result);
							
						if (!$this->results[$currentClass]["object"]) {
							$this->results[$currentClass]["object"] = $this->targets[$currentClass]["object"];
						}
						
						if ($searchTermCount > 0) {
							$occuranceCount = 0;
							
							for ($keyI=0; $keyI < $searchKeyCount; $keyI++) {
								$currentKey = $searchKeys[$keyI];
	
								$haystack = strtolower($row[$this->targets[$currentClass]["object"]->objectDBConnections[$currentKey][0]]);
								
								for ($termI=0; $termI < $searchTermCount; $termI++) {
									$occuranceCount += substr_count($haystack,strtolower($this->targets[$currentClass]["criteria"]["terms"][$termI]));
								}
							}
						}
						else { $occuranceCount = 0; }
	
						if (!is_array($this->results[$currentClass]["ids"])) { $this->results[$currentClass]["ids"] = array(); }
	
						if (!array_key_exists($row[$this->targets[$currentClass]["object"]->objectDBConnections["id"][0]],$this->results[$currentClass]["ids"])) {
							$this->results[$currentClass]["ids"][$row[$this->targets[$currentClass]["object"]->objectDBConnections["id"][0]]] = $occuranceCount;
						}
						else { $this->results[$currentClass]["ids"][$row[$this->targets[$currentClass]["object"]->objectDBConnections["id"][0]]] += $occuranceCount; }
						
						next($result);
					}
				}
			}
		}
		else {
			// Error. Somehow we got our searchmode set incorrectly.
		}
	}
	
	function getResults() {
		if (is_null($this->results)) {
			$this->search();
			return $this->results;
		}
		else { return $this->results; }
	}

	function resultCount($objectClass=null) {
		if (is_object($objectClass)) { $objectClass = get_class($objectClass); }
	
		if (!is_null($this->getResults())) {
			$classes = array_keys($this->getResults());
			$classCount = count($classes);
			
			if ($classCount > 0) {
				if (!$objectClass) { $objectClass = $classes[0]; }
		
				if (is_Null($this->results[$objectClass]["resultCount"])) {
					if (!is_Null($this->results[$objectClass]["ids"])) {
						$this->results[$objectClass]["resultCount"] = count($this->results[$objectClass]["ids"]);
						return $this->results[$objectClass]["resultCount"];
					}
					else {
						// phError("$this - There were no ids retreived for the object class <b>$objectClass</b> yet. Likely search has not been performed.");
						return null;
					}
				}
				else { return $this->results[$objectClass]["resultCount"]; }
			}
		}
		else { return null; }
	}
	
	function resultIDsForClass($class=null) {
		$results = $this->getResults();
		
		if (!(is_null($results)) && !(is_null($class))) {
			if (is_object($class)) { return array_keys($this->results[get_class($class)]["ids"]); }
			else { return array_keys($this->results[$class]["ids"]); }
		}
		else { return null; }
	}

	function produceSearchTermSQL($term,$array=null) {
		if (!is_null($array)) { return "($term LIKE \"%" . implode("%\" OR $term LIKE \"%",$array) . "%\")"; }
	}
	
	function seperateTerms($string) {
		if ($string != "") { $termArray = explode(" ",$string); }
		
		$finalTermArray = array();
		$tempTermArray = array();
		$qCount = 0;
		$inQuoted = FALSE;
		
		$termCount = count($termArray);
	
		for ($i=0;$i < $termCount;$i++) {
			if (!is_bool(strpos($termArray[$i],'"'))) {				// If term contains a quote
				$qCount = substr_count($termArray[$i],'"'); 		// Count # of quotes
				
				if ($qCount == 1 && (strpos($termArray[$i],'"') == 0)) {
					// Start quoted term
					$tempTermArray[] = substr($termArray[$i],1);
					$inQuoted = TRUE;
				}
				else if ($qCount == 1 && (strpos($termArray[$i],'"') == (strlen($termArray[$i]) - 1))) {
					// End quoted term
					$tempTermArray[] = substr($termArray[$i],0,-1);
					$finalTermArray[] = implode(" ",$tempTermArray);
					$tempTermArray = array();
					$inQuoted = FALSE;
				}
				else if ($qCount == 2) {
					// Single word quoted term
					$finalTermArray[] = str_replace('"',"",$termArray[$i]);
					$inQuoted = FALSE;
				}
			}
			else if ($inQuoted) {
				// Aditional word to the quoted term
				$tempTermArray[] = $termArray[$i];
				
				if ($i == $termCount - 1) {
					$finalTermArray[] = implode(" ",$tempTermArray);
					$tempTermArray = array();
					$inQuoted = FALSE;
				}
			}
			else if (substr_count($termArray[$i],"AND") > 0 || substr_count($termArray[$i],"and") > 0) { }
			//else if (isEmail($termArray[$i]) && !$finalTermArray) { $finalTermArray[email] = $termArray[$i]; }
			else { $finalTermArray[] = $termArray[$i]; }
		}
	
		return $finalTermArray;
	}
}

?>