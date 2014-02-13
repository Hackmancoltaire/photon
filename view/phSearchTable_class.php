<?php

// Class Requirements - Must load these before loading.

require_once("phTable_class.php");

class phSearchTable extends phTable {
	
	var $displayMode;
	var $showRank;
	var $objectHeaderTitles;
	var $objectColumnIdentifiers;
	var $objectLinks;
	var $objectOrder;

	function phSearchTable($tableName=NULL) {
		parent::phTable($tableName);
		
		$this->showRank = TRUE;
		$this->displayMode = 1;
		$this->objectHeaderTitles = array();
		$this->objectColumnIdentifiers = array();
		$this->objectLinks = array();
		$this->objectOrder = array();
	}

	function setDisplayMode($mode=1) {
		if ($mode == 1 || $mode == "Standard") { $this->displayMode = 1; }
		else if ($mode == 2 || $mode == "Spotlight") {
			$this->displayMode = 2;

			// Display header for this object class
			$this->setColumnTitles(array("Name","Date"));
			$this->setColumnIdentifiers(array("name","date"));
			$this->setLinkAndKeyForColumn("%linkKey%","link","name");
			$this->setShowColumnHeaders(FALSE);
		}
		else { $this->displayMode = 1; }
	}

	function setShowRank($show) {
		if (is_bool($show)) { $this->showRank = $show; }
	}
	
	function setObjectHeaderTitle($className=NULL,$title=NULL) {
		if ($className && $title) {
			$this->objectHeaderTitles[strtolower($className)] = $title;
		}
	}
	
	function setObjectOrder($orderArray) {
		if (is_array($orderArray)) { $this->objectOrder = $orderArray; }
	}
	
	function setColumnIdentifiersForObject($className=NULL,$identifierArray) {
		if ($className && is_array($identifierArray)) {
			$this->objectColumnIdentifiers[$className] = $identifierArray;
		}
	}
	
	function setLinkAndKeyForObject($link=NULL,$key=NULL,$className=NULL) {
		if (!is_null($className) && !is_null($link) && !is_null($key)) {
			$this->objectLinks[$className] = array(
				"link"	=> $link,
				"key"	=> $key
			);
		}
	}
	
	function addResults($resultArray) {
		global $imageLoc;

		if (is_array($resultArray)) {
			$classes = array_keys($resultArray);
			$classCount = count($classes);
			
			if ($classCount > 0) {
				for ($classI=0; $classI < $classCount; $classI++) {
					$currentClass = $classes[$classI];
					
					$ids = array_keys($resultArray[$currentClass]["ids"]);
					$idCount = count($ids);
					
					$columnWidth = ($this->showRank) ? 3:2;
					
					if ($idCount > 0 && $this->displayMode == 2) {
						$this->addRow(array(
							array("html","<div class='searchHeader'><img src='" . phImage("spacer.gif") . "' width='5' height='19' align='top'><img src='" . phImage("phSearchTable/searchHeader_arrow.gif") . "' align='top'><img src='" . phImage("spacer.gif") . "' width='5' height='19' align='top'>" . $this->objectHeaderTitles[strtolower($currentClass)] . "</div>",$columnWidth)
						));
					}

					// Show the rank column?
					if ($this->showRank) {
						$tempArray = $this->columnTitles;
						$tempArray[] = "Rank";
						$this->setColumnTitles($tempArray);

						$tempArray = $this->columnIdentifiers;
						$tempArray[] = "phSearchRank";
						$this->setColumnIdentifiers($tempArray);
						
						$rankValues = array_values($resultArray[$currentClass]["ids"]);
						rsort($rankValues,SORT_NUMERIC);
						$maximum = array_shift($rankValues);
						$tempArray = $this->columnTypes;
						$tempArray["phSearchRank"] = array(
							type => "HTMLFunction",
							actions => "rankTable(%value%,$maximum)"
						);
						$this->setColumnTypes($tempArray);
					}
					
					for ($idI=0; $idI < $idCount; $idI++) {
						// Reset current object for re-use
						$currentObject = null;
						$currentObject = clone $resultArray[$currentClass]["object"];
						$currentObject->initWithID($ids[$idI]);
						
						if ($currentObject->valid) {
							if ($this->showRank) {
								$currentObject->phSearchRank = $resultArray[$currentClass][ids][$ids[$idI]];
							}

							if ($this->displayMode == 1) {
								// If this object is appearing with other objects in a standard table we need to know how to link it
								if ($this->objectLinks[$currentClass]) { $currentObject->phLink = $this->objectLinks[$currentClass]; }

								$this->addRow($currentObject);
							}
							else {							
								eval('$columnA = $currentObject->' . $this->objectColumnIdentifiers[$currentClass][0] . ';');
								eval('$columnB = $currentObject->' . $this->objectColumnIdentifiers[$currentClass][1] . ';');

								if ($this->objectLinks[$currentClass]) {
									$key = $this->objectLinks[$currentClass]["key"];
									$link = $this->objectLinks[$currentClass]["link"];
									
									$resultLink = str_replace("%linkKey%",$currentObject->$key,$link);
								}
								else { $resultLink = null; }

								$bridgeObject = new phObjectBridge($columnA,$columnB,$currentObject->phSearchRank,$resultLink);

								$this->addRow($bridgeObject);
							}
						}
						else {
							// Object not valid
						}
					}
				}
			}
			else {
				// No Results. Again, how did we get here?
			}
		}
	}
}

// This class is specifically for passing object values through it to the phSearchTable

class phObjectBridge {
	var $name;
	var $link;
	var $date;
	var $psSearchRank;
	
	function phObjectBridge($name=NULL,$date=NULL,$rank=NULL,$link=NULL) {
		if ($name) { $this->name = $name; }
		if ($date) { $this->date = $date; }
		if (!is_Null($rank)) { $this->phSearchRank = $rank; }
		if (!is_Null($link)) { $this->link = $link; }
	}
}

// This is declared outside the class so it can be used inside tables.

function rankTable($current,$maximum) {
	global $imageLoc;

	$width = ((!$current) ? "0":(intVal(($current / $maximum) * 100))) . "%";

	return "<table width='100%' border='0' cellpadding='0' cellspacing='0' height='17'><tr><td width='$width' background='" . phImage("phSearchTable/searchRank.gif") . "'><img src='" . phImage("spacer.gif") . "' width='1' height='1'></td><td><img src='" . phImage("spacer.gif") . "' width='1' height='1'></td></tr></table>";
}

?>