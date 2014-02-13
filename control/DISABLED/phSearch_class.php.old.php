<?php

// PHOTON Search Class
// At this time phSearch can only use the MySQL adaptor to save searches.
// Possibly this will change into saving searches into the session.
// IMPORTANT: Also at this time the search class can only search MySQL databases.
//		this will change as soon as I can extrapolate the search terms from the db.

class phSearch extends MySQL4Adaptor {

	var $objectDBConnections = array(
		id						=> array("phSearchID",1),
		searchTerms				=> array("searchTerms",2),
		searchTermCount 		=> array("searchTermCount",1),
		searchAbsolutes			=> array("searchAbsolutes",5),
		searchAbsoluteCount		=> array("searchAbsoluteCount",1),
		targets					=> array("targets",5)
	);
	
	var $id;
	var $searchTerms;
	var $searchTermCount;
	var $searchAbsolutes;
	var $searchAbsoluteCount;
	var $searchSorts;
	var $searchSortCount;
	
	var $targets;	// Objects and Filter criteria.
	var $results;	// Object type result and rank of result.
	var $resultCount;

	function phSearch($id=NULL) {
		$this->tableName = "saved_searches";
		$this->valid = false;
		
		if ($id) { $this->initWithID($id); }
	}
	
	function addTargetObjectWithFilter($object,$filter=NULL) {
		// $object is a class with an objectDBConnections array.
		// $filter is an array of keys that match keys of the objectDBConnections array.
		
		$className = get_class($object);
		
		if (!$this->targets[$className]) {
			// If there is no target object already in the target array, add it!
			
			$this->targets[$className] = array(
				object	=>	$object,
				filter	=>	$filter
			);
		}
	}
	
	function setSearchCriteria($criteria) { $this->setSearchCriteriaForTarget($criteria); }
	
	function setSearchCriteriaForTarget($criteria,$target=null) {
		if (is_array($criteria)) {
			if (!is_NUll($criteria["terms"])) {
				$this->searchTerms = $criteria["terms"];
				$this->searchTermCount = count($this->searchTerms);
			}
			else { $this->searchTermCount = 0; }

			if (!is_NUll($criteria["absolutes"])) {
				$this->searchAbsolutes = $criteria["absolutes"];
				$searchAbsoluteKeys = array_keys($this->searchAbsolutes);
				$this->searchAbsoluteCount = count($searchAbsoluteKeys);
			}
			else { $this->searchAbsoluteCount = 0; }
			
			if (!is_null($criteria["sorts"])) {
				$sortKeys = array_keys($criteria["sorts"]);
				
				if (!is_null($sortKeys)) {
					$this->searchSorts = $criteria["sorts"];
					$this->searchSortCount = count($sortKeys);
				}
			}
			else { $this->searchSortCount = 0; }
		}
		else {
			$this->searchTerms = $this->seperateTerms($criteria);
			$this->searchTermCount = count($this->searchTerms);
		}
		
		// print_r($this->searchTerms);
		// print_r($this->searchAbsolutes);	
	}
	
	function search($criteria = NULL) {		
		if ($criteria) { $this->setSearchCriteria($criteria); }
	
		$classes = array_keys($this->targets);
		$classesCount = count($classes);
		
		for ($objectI=0; $objectI < $classesCount; $objectI++) {
			// Reset for a new object
			$currentClass = $classes[$objectI];
			$searchCriteria = array();
			$absoluteCriteria = array();

			// If we have any absolutes set in the search criteria we need to set them up in the query.
			if ($this->searchAbsoluteCount > 0) {
				$searchAbsoluteKeys = array_keys($this->searchAbsolutes);

				$absoluteCriteria = array();
				
				for ($absoluteI=0; $absoluteI < $this->searchAbsoluteCount; $absoluteI++) {
					$absoluteModifierValueCount = count($this->searchAbsolutes[$searchAbsoluteKeys[$absoluteI]]);

					if ($absoluteModifierValueCount % 2 == 0) {							
						for ($aMVC=0; $aMVC < $absoluteModifierValueCount - 1; $aMVC += 2) {
							$absoluteCriteria[] = $this->targets[$currentClass][object]->objectDBConnections[$searchAbsoluteKeys[$absoluteI]][0] . 
								$this->searchAbsolutes[$searchAbsoluteKeys[$absoluteI]][$aMVC] .
								$this->searchAbsolutes[$searchAbsoluteKeys[$absoluteI]][$aMVC+1];
						}
					}
					else {
						// phError("$this - Absolute parameters are uneven.");
					}
				}
			}
			else { $absoluteCriteria = array(); }

			// Now depending on if we have search terms set. We setup our search parameters
			if ($this->searchTermCount > 0) {
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
	
				for ($keyI=0; $keyI < $searchKeyCount; $keyI++) {
					$currentKey = $searchKeys[$keyI];
					
					if (!is_Null($currentKey)) {	
						$searchCriteria[] = $this->produceSearchTermSQL($this->targets[$currentClass][object]->objectDBConnections[$currentKey][0],$this->searchTerms);
					}
				}
			}

			$queryString = "SELECT DISTINCT * FROM " . $this->targets[$currentClass][object]->tableName . " WHERE ";

			// Add the search term criteria onto the end of the queryString.
			if ($this->searchTermCount > 0) {
				$queryString .= implode(" OR ",$searchCriteria);
			}

			// Add the absolute criteria onto the end of the queryString.
			if (count($absoluteCriteria) > 0) {
				$queryString .= (($this->searchTermCount > 0) ? " AND ":"") . implode(" AND ",$absoluteCriteria);
			}
			
			// Include sorting method
			if ($this->searchSortCount > 0) {
				$sortKeys = array_keys($this->searchSorts);
			
				$queryString .= " ORDER BY ";
				
				for ($sortI=0; $sortI < $this->searchSortCount; $sortI++) {
					$queryString .= $sortKeys[$sortI] . " " . $this->searchSorts[$sortKeys[$sortI]];
					
					if ($sortI != $this->searchSortCount - 1) {
						$queryString .= ", ";
					}
				}
			}

			// Uncomment here to see the query string.
			// print $queryString . "\n";
			
			$result = $this->targets[$currentClass]["object"]->query($queryString);
			
			if (!$result) { error(); }
			else {
				while($row = mysql_fetch_array($result)) {
					if (!$this->results[$currentClass]["object"]) {
						$this->results[$currentClass]["object"] = $this->targets[$currentClass]["object"];
					}

					if ($this->searchTermCount > 0) {
						$occuranceCount = 0;
						
						for ($keyI=0; $keyI < $searchKeyCount; $keyI++) {
							$currentKey = $searchKeys[$keyI];

							$haystack = strtolower($row[$this->targets[$currentClass]["object"]->objectDBConnections[$currentKey][0]]);
							
							for ($termI=0; $termI < $this->searchTermCount; $termI++) {
								$occuranceCount += substr_count($haystack,strtolower($this->searchTerms[$termI]));
							}
						}
					}
					else { $occuranceCount = 0; }

					if (is_Null($this->results[$currentClass]["ids"][$row[$this->targets[$currentClass]["object"]->objectDBConnections["id"][0]]])) {
						$this->results[$currentClass]["ids"][$row[$this->targets[$currentClass]["object"]->objectDBConnections["id"][0]]] = $occuranceCount;
					}
					else { $this->results[$currentClass]["ids"][$row[$this->targets[$currentClass]["object"]->objectDBConnections["id"][0]]] += $occuranceCount; }
				}
			}
		}
	}
	
	function getResults() { return $this->results; }

	function resultCount($objectClass=null) {
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