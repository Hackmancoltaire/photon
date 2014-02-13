<?php

class phTable {
	
	// PHOTON Table Class - 2007
	// This class is intended to display data in a table format.
	// The logic for displaying this table is contained herein.
	// But most of the look and feel for the table is defined in
	// PHOTON's main CSS file. To use your own style for the table
	// you must copy the structure from the CSS file and give the
	// table class a different name. Then by using the setStyleClass
	// function you can change the style used to render each table.
	// In this way you can have multiple table styles while using
	// the same class.
	
	var $name;

	var $columnTitles;			// Array
	var $columnIdentifiers;		// Array
	var $columnWidths;			// Array
	var $columnTypes;			// Array
	var $showColumnHeaders;		// Boolean
		
	var $columnCount;			// Int
	var $selectedColumn;		// Int
	var $linkedColumns;			// Array
	
	var $content;				// Array
	var $rowCount;				// Int
	var $sortKey;				// String
	var $sortOrder;				// String: ASC or DESC
		
	var $autoColor;				// Boolean
	var $link;					// String (Obsolete)
	var $linkKey;				// String (Obsolete)
	
	var $ATDeleteScript;		// String
	var $ATEditScript;			// String
	var $ATAddScript;			// String
	var $formAction;			// String
	var $additionalFormData;	// String
	
	var $tableStyle;
	
	function phTable($tableName=NULL) {
		$this->content = array();
		$this->linkedColumns = array();
		$this->autoColor = TRUE;
		$this->autoSort = FALSE;
		$this->showColumnHeaders = TRUE;
		$this->rowCount = 0;
		$this->tableStyle = "photonTable";
		
		$this->name = (!$tableName) ? "genericTable":$tableName;
	}
	
	function resetContent() {
		$this->content = NULL;
		$this->content = array();
		$this->rowCount = 0;
	}
	
	function setColumnTitles($columnArray) {
		$this->columnTitles = (is_array($columnArray)) ? $columnArray:NULL;
		
		$this->columnCount = count($this->columnTitles);
	}
	
	function setColumnTypes($typeArray) {
		$this->columnTypes = (is_array($typeArray)) ? $typeArray:NULL;
	}
	
	function setColumnWidths($widthArray) {
		$this->columnWidths = (is_array($widthArray)) ? $widthArray:NULL;
	}
	
	function setColumnIdentifiers($columnIdentifiers) {
		$this->columnIdentifiers = (is_array($columnIdentifiers)) ? $columnIdentifiers:NULL;
	}
	
	function setStyleClass($style=null) {
		if (!is_null($style)) { $this->tableStyle = $style; }
	}
	
	function setShowColumnHeaders($show) {
		// This still requires you to define the number of columns. Use setColumnTitles() with bogus titles.
		
		if (is_bool($show) && ($show == TRUE)) { $this->showColumnHeaders = TRUE; }
		else { $this->showColumnHeaders = FALSE; }
	}
	
	function setLinkAndKeyForColumn($link,$key,$column) {
		if (!is_null($column)) {
			$this->linkedColumns[$column] = array (
				link => $link,
				key => $key
			);
		}
	}
	
	function setAutoSort($autoSort=null) {
		if (!is_null($autoSort) && is_bool($autoSort)) { $this->autoSort = $autoSort; }
	}
	
	function setSelectedColumn($column=null,$sortDirection=null) {
		if (!is_int($column)) { $this->sortKey = $column; }
		else if (!is_null($column)) { $this->selectedColumn = $column; }
		
		if (!is_null($sortDirection)) { $this->sortOrder = $sortDirection; }
	}
	
	function setSecondarySort($field,$order) {
		//print "field = $field / order = $order<BR>";
		if ($field !== NULL) {
			$_SESSION['_AT_' . $this->name . '_sorterIdentifier2'] = $field;
		}

		$secondaryOrder = NULL;
		$fieldType = getType($order);
		if ($fieldType == "integer") {
			$secondaryOrder = $order;
		}
		else if ($fieldType == "string") {
			$secondaryOrder = ($order == "DESC") ? -1:1;
		}
		if ($secondaryOrder !== NULL) {
			$_SESSION['_AT_' . $this->name . '_order2'] = $secondaryOrder;
		}
	}

	function addRow($rowArray,$triggersRowColor="AUTO",$rowColor=NULL,$disable=FALSE) {
		if (is_array($rowArray) || is_object($rowArray)) {
			$this->content[] = array(
				"row"			=> $rowArray,
				"colorTrigger"	=> $triggersRowColor,
				"rowColor"		=> $rowColor,
				"sortable"		=> is_object($rowArray),
				"disable"		=> $disable
			);
		}
		else if ($rowArray == "hr") { $this->content[] = "hr"; }
		else if ($rowArray == "spacer") { $this->content[] = "spacer"; }
		else { }
		
		$this->rowCount++;
		
		return $this->rowCount-1;
	}
	
	function disableRow($rowID=null) {
		if (!is_null($rowID)) { $this->content[$rowID]["disable"] = TRUE; }
	}
	
	function compareObjectsWithIdentifier($a,$b,$columnID) {
		eval('$aVal = $a["row"]->' . $this->columnIdentifiers[$columnID] . ';');
		eval('$bVal = $b["row"]->' . $this->columnIdentifiers[$columnID] . ';');

		// Test for NULLs first so that they can go at the end of the sort.
		if (is_null($aVal)) { return -1; }
		else if (is_null($bVal)) { return 1; }
		else if (getType($aVal) == getType($bVal)) {
			$compareType = getType($aVal);
			
			if ($compareType == "boolean") {
				if ($aVal == $bVal) { return 0; }
				else { return (($aVal == TRUE) ? 1:-1); }
			}
			else if (is_numeric($aVal) && is_numeric($bVal)) { return (($aVal == $bVal) ? 0: (($aVal > $bVal) ? 1 : -1)); }
			else if ($compareType == "string") { return strcasecmp($aVal,$bVal); }
			else if ($compareType == "NULL") { return 0; }
			else { return 0; }
		}
		else { return 0; }
	}
	
	function sortContentObjects($a,$b) {
		// Compares content rows
		
		if (session_id() != NULL) { $order = $_SESSION['_AT_' . $this->name . '_lastOrder']; }
		else { $order = ($this->sortOrder == "ASC") ? -1:1; }
		
		if (getType($a["row"]) == "object" && getType($b["row"]) == "object") {
			$returnValue = $this->compareObjectsWithIdentifier($a,$b,$this->selectedColumn);
			if ($returnValue == 0) {
				if (session_id() != NULL && $_SESSION['_AT_' . $this->name . '_sorterIdentifier2'] !== NULL) {
					$order = $_SESSION['_AT_' . $this->name . '_order2'];
					$returnValue = $this->compareObjectsWithIdentifier($a,$b,$_SESSION['_AT_' . $this->name . '_sorterIdentifier2']);
				}
			}
			$returnValue = $returnValue * $order;
			return $returnValue;
		}
		else { return 0; }
	}
	
	function sortContent() {
		$sortedContent = array();
		$tempSortedContent = array();
		
		$contentCount = count($this->content);
		$sortableContentCount = 0;
		
		for($i=0; $i < $contentCount; $i++) {
			if (is_array($this->content[$i]) && $this->content[$i][sortable]) {
				$tempSortedContent[] = $this->content[$i];
				$sortableContentCount++;
			}
			else {
				if ($sortableContentCount > 0) {
					usort($tempSortedContent,array($this,"sortContentObjects"));
					$sortedContent = array_merge($sortedContent,$tempSortedContent);
					$tempSortedContent = array();
					$sortableContentCount = 0;
				}
				
				$sortedContent[] = $this->content[$i];
			}
		}
		
		if ($sortableContentCount > 0) {
			usort($tempSortedContent,array($this,"sortContentObjects"));
			$sortedContent = array_merge($sortedContent,$tempSortedContent);
		}
		
		$this->content = $sortedContent;
	}
	
	function ATButton($type,$formName,$id,$cellWrapper=true,$disable=FALSE,$disableTypeChecking=false) {		
		if ($type == "Add") { $buttonType = "plus"; }
		else if ($type == "Edit") { $buttonType = "editPencil"; }
		else if ($type == "Delete") { $buttonType = "minus"; }
		else { $buttonType = "spacer"; }
		
		if ($disable) {
			$buttonType .= "_disabled";
		
			return (($cellWrapper) ? "<td align='center' id='button'>":"") . "<img src='" . phImage($buttonType . ".gif") . "' border='0'>" . (($cellWrapper) ? "</td>":"");
		}
		else {
			return (($cellWrapper) ? "<td align='center' id='button'>":"") . 
				"<a id='button' href=\"javascript:" . $formName . "_AT" . $type . "(" . ((is_numeric($id) || $disableTypeChecking) ? $id:"'" . $id . "'") . ")\"><img src='" . phImage($buttonType . ".gif") . "' border='0'></a>" .
				(($cellWrapper) ? "</td>":"");
		}
	}
	
	function display($returnMode = "print") {
		$returnedText = "";
		
		if (!$this->selectedColumn) { $this->selectedColumn = 0; }
		else if ($this->selectedColumn == -1) { $this->autoSort = FALSE; }
		
		if ($this->formAction) {
			$returnedText .= "<script type='text/javascript' language='Javascript'>
				function " . $this->name . "_ATAdd(id) { " . str_replace("%formName%",$this->name,$this->ATAddScript) . "
				}
				function " . $this->name . "_ATDelete(id) { " . str_replace("%formName%",$this->name,$this->ATDeleteScript) . "
				}
				function " . $this->name . "_ATEdit(id) { " . str_replace("%formName%",$this->name,$this->ATEditScript) . "
				}
			</script>
			<form enctype='multipart/form-data' name='$this->name' action='$this->formAction' method='post'>
			" . str_replace("%formName%",$this->name,$this->additionalFormData);
		}
		
		$order = ($this->sortOrder == "ASC") ? 1:-1;
		if ($this->autoSort) {
			$lastIdentifier = $_SESSION['_AT_' . $this->name . '_lastIdentifier'];

			//print "this->selectedColumn = $this->selectedColumn / lastIdentifier = $lastIdentifier<BR>";
			if ($this->selectedColumn != $lastIdentifier) {
				$lastOrder = $_SESSION['_AT_' . $this->name . '_lastOrder'];
				$_SESSION['_AT_' . $this->name . '_lastIdentifier'] = $this->selectedColumn;
				$_SESSION['_AT_' . $this->name . '_lastOrder'] = $order;
				if ($lastIdentifier !== NULL) {
					$this->setSecondarySort($lastIdentifier,$lastOrder);
				}
			}
			else {
				$_SESSION['_AT_' . $this->name . '_lastOrder'] = $order;
			}
		}

		// What will URL LINKs have for a sortOrder
		$this->sortOrder = ($this->sortOrder === NULL) ? "DESC":(($this->sortOrder == "ASC") ? "DESC":"ASC");

		$returnedText .= "<table border='0' cellspacing='0' cellpadding='0' class='" . $this->tableStyle . "'>\n";
		
		if ($this->showColumnHeaders) {
			$returnedText .= "<tr id='header'>\n";
			
			for ($i=0; $i < $this->columnCount; $i++) {
				$sortOnThis = ($this->selectedColumn == $i) ? TRUE:FALSE;
				$state = ($sortOnThis) ? "selected":"unselected";

				$sortOnThis2 = ($_SESSION['_AT_' . $this->name . '_sorterIdentifier2'] !== NULL) && 
							   ($_SESSION['_AT_' . $this->name . '_sorterIdentifier2'] == $i);
				$order2 = $_SESSION['_AT_' . $this->name . '_order2'];

				// Start table column text & fill
				
				if ($this->columnTitles[$i] == "ATAdd" || $this->columnTitles[$i] == "ATDelete" || $this->columnTitles[$i] == "ATEdit") {
					$returnedText .= "<td id='$state' width='22'>\n";
					
					if ($i < $this->columnCount-1) { $returnedText .= "<div id='columnSpacer_$state'></div>"; }
					else { $returnedText .= "<div id='columnSpacer_end'></div>"; }
				}
				else {
					if ($this->autoSort) {
						$columnURL = buildURL($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING'],array($this->name . "_selectedColumn=$i",$this->name . "_sortOrder=".(($sortOnThis) ? "$this->sortOrder" : "ASC")));
					}
					else { $columnURL = "#"; }

					$returnedText .= "<td id='$state'" . ((is_array($this->columnWidths) && is_numeric($this->columnWidths[$i])) ? " width='" . $this->columnWidths[$i] . "'":"") . (($this->autoSort) ? " onClick='window.location.href=\"" . $columnURL . "\"'":"") . "><nobr>\n";
				
					// Start table column right border
					
					if ($i < $this->columnCount-1) { $returnedText .= "<div id='columnSpacer_$state'></div>"; }
					else { $returnedText .= "<div id='columnSpacer_end'></div>"; }
					
					if ($this->autoSort) {
						$returnedText .= (($sortOnThis) ? ("<div id='sort_" . (($order == 1) ? "ascending":"descending") . "'></div>") : (($sortOnThis2) ? ("<div id='sort_unselected_" . (($order2 == 1) ? "ascending":"descending") . "'></div>") : ""));
					}
					
					// Put in column title
					$returnedText .= "<div id='column'>". $this->columnTitles[$i] . "</div></nobr>";
				}				
				
				// End the column
				$returnedText .= "</td>";
			}
		}
		else {
			if (is_null($this->columnCount) || $this->columnCount == 0) { $this->setColumnTitles(array(" ")); }
			
			$returnedText .= "<tr id='header'>";

			for ($i=0; $i < $this->columnCount; $i++) {
				$columnSize = NULL;
				
				if ($this->columnTitles[$i] == "ATAdd" || $this->columnTitles[$i] == "ATDelete" || $this->columnTitles[$i] == "ATEdit") { $columnSize = "20"; }
				else if (is_array($this->columnWidths) && is_numeric($this->columnWidths[$i])) { $columnSize = $this->columnWidths[$i]; }
			
				$returnedText .= "<td height='1'" . (($columnSize != NULL) ? " width='" . $columnSize . "'":"") . "id='noheader'><img src='" . phImage("spacer.gif") . "' height='1'". (($columnSize != NULL) ? " width='" . $columnSize . "'":" width='1'") . "></td>";
			}
		}
		
		// Close the header rows
		
		$returnedText .= "</tr><tbody id='" . $this->name . "'>";
		
		// Start Content Rows
		$coloredRowCount = 0;
		
		if ($this->autoSort) { $this->sortContent(); }
		
		for ($i=0; $i < $this->rowCount; $i++) {
			$rowColor = null;
		
			if ($this->autoColor) {
				if ($this->content[$i]["colorTrigger"] != "AUTO") {
					if ($this->content[$i]["rowColor"]) {
						$rowID = "row";
						$rowColor = $this->content[$i]["rowColor"];
					}
					else {
						$rowID = (($this->content[$i]["colorTrigger"]) ? "alt":"") . "row";
					}
				}
				else { $rowID = ((is_int($coloredRowCount/2)) ? "":"alt") . "row"; }
			}
			else { $rowID = (($this->content[$i]["colorTrigger"] || is_int($coloredRowCount/2)) ? "":"alt") . "row"; }
			
			if ($this->content[$i] == "hr") {
				$returnedText .= tabs(5) . "<tr><td colspan='$this->columnCount' bgcolor='#C3C3C3'><img src='" . phImage("spacer.gif") . "' width='100' height='1'></td></tr>\n";
			}
			else {
				$coloredRowCount++;
				$returnedText .= "<tr name='" . $i . "' id='$rowID'" . (($rowColor) ? " style='background-color: $rowColor'":"") . ">";
				
				for($a=0; $a < $this->columnCount; $a++) {
					$jumpTo = NULL;
					$returnedValue = NULL;
					$tempColumnContent = NULL;
					$typeArray = NULL;
					$data = NULL;
					$condition = NULL;
					$arg = NULL;
					
					if ($this->columnTitles[$a] == "ATSpacer" || (is_array($this->content[$i]["row"]) && $this->content[$i]["row"][$a] == "ATSpacer")) { $returnedText .= "<td></td>"; }
					else if ($this->columnTitles[$a] == "ATAdd" && is_object($this->content[$i]["row"])) {
						if (!$this->columnIdentifiers[$a]) { $returnedText .= $this->ATButton("Add",$this->name,$this->content[$i]["row"]->id,true,$this->content[$i]["disable"]); }
						else {
							$buttonTemp = $this->columnIdentifiers[$a];
							$returnedText .= $this->ATButton("Add",$this->name,$this->content[$i]["row"]->$buttonTemp,true,$this->content[$i]["disable"]);
						}
					}
					else if ($this->columnTitles[$a] == "ATDelete" && is_object($this->content[$i]["row"])) {
						if (!$this->columnIdentifiers[$a]) { $returnedText .= $this->ATButton("Delete",$this->name,$this->content[$i]["row"]->id,true,$this->content[$i]["disable"]); }
						else {
							$buttonTemp = $this->columnIdentifiers[$a];
							$returnedText .= $this->ATButton("Delete",$this->name,$this->content[$i]["row"]->$buttonTemp,true,$this->content[$i]["disable"]);
						}
					}
					else if ($this->columnTitles[$a] == "ATEdit" && is_object($this->content[$i]["row"])) {
						if (!$this->columnIdentifiers[$a]) { $returnedText .= $this->ATButton("Edit",$this->name,$this->content[$i]["row"]->id,true,$this->content[$i]["disable"]); }
						else {
							$buttonTemp = $this->columnIdentifiers[$a];
							$returnedText .= $this->ATButton("Edit",$this->name,$this->content[$i]["row"]->$buttonTemp,true,$this->content[$i]["disable"]);
						}
					}
					else if (!is_object($this->content[$i]["row"]) && is_array($this->content[$i]["row"][$a])) {
						$cellType = $this->content[$i]["row"][$a][0];
						
						if ($cellType == "popup") {
							$selectedPopup = $this->content[$i]["row"][$a][count($this->content[$i]["row"][$a]) - 1];
							$popupCount = count($this->content[$i]["row"][$a]) - 1;

							$returnedText .= "<td><select name='" . $this->content[$i]["row"][$a][1] . "'" . (($this->content[$i]["disabled"]) ? " disabled":"") . ">\n"; // Print select name
											
							for($j=2;$j < $popupCount; $j++) {
								$returnedText .= "<option value='" . $this->content[$i]["row"][$a][$j] . "'" . (($selectedPopup == $this->content[$i]["row"][$a][$j]) ? " selected":"") . ">" . $this->content[$i]["row"][$a][$j] . "\n";
							}
							$returnedText .= "</select></td>\n";
						}
						else if ($cellType == "textfield") {
							$fieldWidth = ($this->content[$i]["row"][$a][3] == "" || $this->content[$i]["row"][$a][3] == NULL) ? 12:$this->content[$i]["row"][$a][3];
							$returnedText .= "<td><input type='textfield' name='" . $this->content[$i]["row"][$a][1] . "' size='$fieldWidth' value='" . $this->content[$i]["row"][$a][2] . "'" . (($this->content[$i]["disabled"]) ? " disabled":"") . "></td>\n";
						}
						else if ($cellType == "textarea") {
							if ($this->content[$i]["row"][$a][2] != "" || $this->content[$i]["row"][$a][2] != NULL) {
								list($width,$height) = explode("x",$this->content[$i]["row"][$a][2]);
								if (!is_numeric($width) || !is_numeric($height)) {
									$width = 10;
									$height = 10;
								}
							}
							else {
								$width = 30;
								$height = 5;
							}
							$returnedText .= "<td><nobr><textarea name='" . $this->content[$i]["row"][$a][1] . "' rows='$height' cols='$width'" . (($this->content[$i]["disabled"]) ? " disabled":"") . ">" . $this->content[$i]["row"][$a][3] . "</textarea></nobr></td>\n";
						}
						else if ($cellType == "checkbox") {
							$returnedText .= "<td width='19'><input type='checkbox' name='" . $this->content[$i]["row"][$a][1] . "'" . (($this->content[$i]["row"][$a][2] == "yes") ? " checked":"") . (($this->content[$i]["disabled"]) ? " disabled":"") . "></td>";
						}
						else if ($cellType == "image") {
							if ($this->content[$i]["row"][$a][1] != "") {
								if ($this->content[$i]["row"][$a][2] == "" || $this->content[$i]["row"][$a][2] == NULL) {
									$returnedText .= "<td align='center'><img src='" . $this->content[$i]["row"][$a][1] . "' border='0'></td>";
								}
								else {
									$returnedText .= "<td align='center'><a href='" . $this->content[$i]["row"][$a][2] . "'><img src='" . $this->content[$i]["row"][$a][1] . "' border='0'></a></td>";
								}
							}
						}
						else if ($cellType == "html") {
							if ($this->content[$i]["row"][$a][2] != "" && $this->content[$i]["row"][$a][2] != NULL) {
								$columns = $this->content[$i]["row"][$a][2];
							}
							else { $columns = 1; }
							
							if ($this->content[$i]["row"][$a][1] != "") {
								$returnedText .= (($columns) ? ("<td colspan='$columns' id='nopadding'>\n"):("<td>\n")) . $this->content[$i]["row"][$a][1] . "\n</td>\n"; 
							}
							if ($columns != 1) { $a = $a + ($this->content[$i]["row"][$a][2] - 1); }
						}
						else if ($cellType == "file") {
							if ($this->content[$i]["row"][$a][2] != "" && $this->content[$i]["row"][$a][2] != NULL) {
								$columns = $this->content[$i]["row"][$a][2];
							}
							else { $columns = 1; }
							
							if ($this->content[$i]["row"][$a][1] != "") {
								$returnedText .= (($columns) ? ("<td colspan='$columns' id='nopadding'>\n"):("<td>\n")) . "<input type='hidden' name='MAX_FILE_SIZE' value='30000000'><input type='file' name='" . $this->content[$i]["row"][$a][1] . "' size='40' style='margin:4px'></td>"; 
							}
							if ($columns != 1) { $a = $a + ($this->content[$i]["row"][$a][2] - 1); }
						}
						else if ($cellType == "ATAdd") { $returnedText .= $this->ATButton("Add",$this->name,$this->content[$i]["row"][$a][1],true,$this->content[$i]["disable"]); }
						else if ($cellType == "ATEdit") { $returnedText .= $this->ATButton("Edit",$this->name,$this->content[$i]["row"][$a][1],true,$this->content[$i]["disable"]); }
						else if ($cellType == "ATDelete") { $returnedText .= $this->ATButton("Delete",$this->name,$this->content[$i]["row"][$a][1],true,$this->content[$i]["disable"]); }
					}
					else {
						if (is_object($this->content[$i]["row"])) {
							if (!is_null($this->columnIdentifiers[$a])) {
								eval('$returnedValue = $this->content[$i]["row"]->' . $this->columnIdentifiers[$a] . ';');
							}
						}
						else {
							list($data,$condition,$arg) = explode("%", $this->content[$i]["row"][$a]);
							$returnedValue = $data;	
						}
						
						if (!is_null($returnedValue) && $returnedValue != "") {
							// Apply column type to the returned value.
							if (is_array($this->columnTypes[$a]) && !is_null($returnedValue)) {
								$typeArray = $this->columnTypes[$a];
							}
							else if (is_array($this->columnTypes[$this->columnIdentifiers[$a]]) && !is_null($returnedValue)) {
								$typeArray = $this->columnTypes[$this->columnIdentifiers[$a]];
							}
							else { $typeArray = NULL; }

							if (!is_null($typeArray) && $this->content[$i]["sortable"]) {
								if ($typeArray["type"] == "bool") { $actionKey = ($returnedValue) ? 0:1; }
								else if ($typeArray["type"] == "case") { $actionKey = $returnedValue; }
								else if ($typeArray["type"] == "function") { $actionKey = $returnedValue; }
								else if ($typeArray["type"] == "popup") { $actionKey = $returnedValue; }
								else if ($typeArray["type"] == "HTMLFunction") { $actionKey = $returnedValue; }
								else {
									// No other column types yet.
								}
								
								if (is_array($typeArray["actions"])) {
									if (is_array($typeArray["actions"][$actionKey])) {
										// Action key found
										if ($typeArray["actions"][$actionKey]["type"] == "image") { $tempColumnContent = "<img src='" . $typeArray["actions"][$actionKey]["value"] . "'>"; }
										else if ($typeArray["actions"][$actionKey]["type"] == "text") { $tempColumnContent = $typeArray["actions"][$actionKey]["value"]; }
										else {
											// No type that I recognize.
										}
									}
									else {
										// Action key not found but this is only valid for case type columns
										if ($typeArray["type"] == "case" && !is_null($typeArray["actions"]["OTHER"])) {
											// There was an OTHER action, which means we should perform this action in the case that a predetermined value is not present
											
											if ($typeArray["actions"]["OTHER"]["type"] == "image") { $tempColumnContent = "<img src='" . $typeArray["actions"]["OTHER"]["value"] . "'>"; }
											else if ($typeArray["actions"]["OTHER"]["type"] == "text") { $tempColumnContent = $typeArray["actions"]["OTHER"]["value"]; }
											else {
												// No type that I recognize.
											}
										}
									}
								}
								else {
									if ($typeArray["type"] == "function" || $typeArray["type"] == "HTMLFunction") {
										$action = str_replace("%value%",$actionKey,$typeArray["actions"]);
										eval('$returnedValue = ' . $action . ';');
									}
									else if ($typeArray["type"] == "popup") {
										$typeArray["actions"]->setDisabled($this->content[$i]["disable"]);
										$tempColumnContent = $typeArray["actions"]->display("return",$returnedValue);
									}
									else {}
								}
							}
							else {
								// Normal textual column. Take the returned value as a pointer instead of getting the value again.
								$tempColumnContent = &$returnedValue;
							}
							
							// If the result of our function OR the result of the object attribute (which is HIGHLY frowned upon) is to show a button then display it.
							
							if ($tempColumnContent == "ATAdd") { $returnedText .= $this->ATButton("Add",$this->name,$this->content[$i]["row"]->id); }
							else if ($tempColumnContent == "ATDelete") { $returnedText .= $this->ATButton("Delete",$this->name,$this->content[$i]["row"]->id); }
							else if ($tempColumnContent == "ATEdit") { $returnedText .= $this->ATButton("Edit",$this->name,$this->content[$i]["row"]->id); }
							else {
								// Else get ready to display the text.
	
								// This is only valid for non-object rows.
								if ($condition == "span") {
									if ($arg && is_numeric($arg)) {
										$columns = $arg;
										$jumpTo = $a + $arg - 1;
									}
									else if (!$arg) {
										$columns = $this->columnCount;
										$jumpTo = $a + $this->columnCount - 1;
									}
									else { $columns = 1; }
									
									$returnedText .= "<td colspan='$columns'>";
								}
								else { $returnedText .= "<td>"; } // Object rows display this.
								
								// Add a link to the cell
								
								$link = NULL;
								$key = NULL;
								
								if (!is_null($this->linkedColumns[$a])) {
									$link = $this->linkedColumns[$a][link];
									$key = $this->linkedColumns[$a][key];
								}
								else if (!is_null($this->linkedColumns[$this->columnIdentifiers[$a]])) {
									$link = $this->linkedColumns[$this->columnIdentifiers[$a]][link];
									$key = $this->linkedColumns[$this->columnIdentifiers[$a]][key];
								}
								else if ($a == $this->linkedColumn) {
									$link = $this->link;
									$key = $this->linkKey;
								}
								
								if (!is_null($this->content[$i]["row"]->phLink)) {
									$link = $this->content[$i]["row"]->phLink["link"];
									$key = $this->content[$i]["row"]->phLink["key"];
								}
								
								if ($condition == "link" && !$this->content[$i]["disable"]) {
									$returnedText .= "<a href='$arg' class='defaultText' border='0'>$tempColumnContent</a>";
								}
								else if (!is_null($link) && !is_null($key) && $this->content[$i]["sortable"] && !$this->content[$i]["disable"]) {
									$returnedText .= "<a href='" . str_replace("%linkKey%",$this->content[$i]["row"]->$key,$link) . "' class='defaultText' border='0'>" . $tempColumnContent . "</a>";
								}
								else { $returnedText .= $tempColumnContent; }	
							}
							
							$returnedText .= "</td>\n"; // Close the column.
						}
						else {
							// Value was empty OR NULL, print blank cell.
							$returnedText .= "<td></td>\n";
						}
						
						// If we setup a span earlier we need to jump to that column for this row.
						if (!is_null($jumpTo) && $jumpTo !=0) { $a = $jumpTo; }
					}
				}
		
				$returnedText .= "</tr>\n";
			}
		}
		
		// End Content Rows and Close Table
		$returnedText .= "</tbody></table>\n";
		
		if ($this->formAction) { $returnedText .= "</form>\n"; }
		
		if ($returnMode == "print") { print $returnedText; }
		else { return $returnedText; }
	}
}

?>