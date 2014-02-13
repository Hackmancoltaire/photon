<?php

class phAquaTable {
	
	// UI Element: Aqua Table
	// Supports sorting

	var $name;

	var $columnTitles;		// Array
	var $columnIdentifiers;	// Array
	var $columnWidths;		// Array
	var $columnTypes;		// Array
	var $showColumnHeaders;	// Boolean
	
	var $columnCount;		// Int
	var $innerColspan;		// Int
	var $outerColspan;		// Int
	var $selectedColumn;	// Int
	var $linkedColumn;		// Int (Obsolete)
	var $linkedColumns;		// Array

	var $content;			// Array
	var $rowCount;			// Int
	var $sortKey;			// String
	var $sortOrder;			// String: ASC or DESC
	
	var $rowColor;			// String
	var $alternateRowColor;	// String
	var $autoColor;			// Boolean
	var $link;				// String (Obsolete)
	var $linkKey;			// String (Obsolete)
	
	var $ATDeleteScript;		// String
	var $ATEditScript;			// String
	var $ATAddScript;			// String
	var $formAction;			// String
	var $additionalFormData;	// String
	
	function phAquaTable($tableName=NULL) {
		$this->content = array();
		$this->linkedColumns = array();
		$this->rowColor = "#ECF2FF"; // Aqua Blue
		$this->alternateRowColor = "#FFFFFF"; // White
		$this->autoColor = TRUE;
		$this->autoSort = FALSE;
		$this->showColumnHeaders = TRUE;
		$this->rowCount = 0;
		
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
		$this->innerColspan = ($this->columnCount * 2) - 1;
		$this->outerColspan = ($this->columnCount * 2) + 1;
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

	function addRow($rowArray,$triggersRowColor = "AUTO",$rowColor = NULL) {
		if (is_array($rowArray) || is_object($rowArray)) {
			$this->content[] = array(
				"row" => $rowArray,
				"colorTrigger" => $triggersRowColor,
				"rowColor" => $rowColor,
				"sortable" => is_object($rowArray)
			);
		}
		else if ($rowArray == "hr") { $this->content[] = "hr"; }
		else if ($rowArray == "spacer") { $this->content[] = "spacer"; }
		else { }
		
		$this->rowCount++;
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
			else if ($compareType == "integer" || $compareType == "double") { return (($aVal == $bVal) ? 0: (($aVal > $bVal) ? 1 : -1)); }
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
	
	function ATButton($type,$formName,$id,$cellWrapper=true) {		
		if ($type == "Add") { $buttonType = "plus"; }
		else if ($type == "Edit") { $buttonType = "editPencil"; }
		else if ($type == "Delete") { $buttonType = "minus"; }
		else { $buttonType = "spacer"; }
		
		return (($cellWrapper)?"<td align='center'>":"") . 
				"<a href='javascript:" . $formName . "_AT" . $type . "(" . $id . ")'><img src='" . phImage($buttonType . ".gif") . "' border='0'>" .
				(($cellWrapper)?"</td>":"");
	}
	
	function display($returnMode = "print") {
		// This is a temporary hack....do the right thing....Mr. President!
		$imageLoc = $GLOBALS["registeredLocations"]["Images"][0];
		
		$returnedText = "";
		
		if (!$this->selectedColumn) { $this->selectedColumn = 0; }
		else if ($this->selectedColumn == -1) { $this->autoSort = FALSE; }
		
		if ($this->formAction) {
			$returnedText .= "<script type='text/javascript' language='Javascript'>
				function " . $this->name . "_ATAdd() { " . str_replace("%formName%",$this->name,$this->ATAddScript) . "
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

		$returnedText .= "<table border='0' cellpadding='0' cellspacing='0' width='100%'>";
		
		if ($this->showColumnHeaders) {
			$returnedText .= "<tr>";
			
			for ($i=0; $i < $this->columnCount; $i++) {
				$sortOnThis = ($this->selectedColumn == $i) ? TRUE:FALSE;
				$state = ($sortOnThis) ? "selected":"unselected";

				$sortOnThis2 = ($_SESSION['_AT_' . $this->name . '_sorterIdentifier2'] !== NULL) && 
							   ($_SESSION['_AT_' . $this->name . '_sorterIdentifier2'] == $i);
				$order2 = $_SESSION['_AT_' . $this->name . '_order2'];

				// Start table column left border
				if ($i == 0) { $returnedText .= "<td width='1'><img src='$imageLoc/phAquaTable/border_$state.gif' width='1' height='17'></td>"; }

				// Start table column text & fill
				
				if ($this->columnTitles[$i] == "ATAdd" || $this->columnTitles[$i] == "ATDelete" || $this->columnTitles[$i] == "ATEdit") {
					$returnedText .= "<td width='18' background='$imageLoc/phAquaTable/fill_$state.gif'><img src='$imageLoc/spacer.gif' width='20' height='8'></td>";
				}
				else {
					$returnedText .= "<td" . ((is_array($this->columnWidths) && is_numeric($this->columnWidths[$i])) ? " width='" . $this->columnWidths[$i] . "'":"") . " background='$imageLoc/phAquaTable/fill_$state.gif'>";
					
					if ($this->autoSort) {
						$returnedText .= "<table border='0' cellpadding='0' cellspacing='0' width='100%' onclick='javascript:window.location.href=\"" . buildURL($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING'],array($this->name . "_selectedColumn=$i",$this->name . "_sortOrder=".(($sortOnThis) ? "$this->sortOrder" : "ASC"))) . "\"'>
							<tr>
								<td width='8'><img src='$imageLoc/spacer.gif' width='8' height='8'></td>
								<td><nobr><a href='" . buildURL($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING'],array($this->name . "_selectedColumn=$i",$this->name . "_sortOrder=".(($sortOnThis) ? "$this->sortOrder" : "ASC"))) . "' class='tableHeader'>". $this->columnTitles[$i] . "</a></nobr></td>
								<td></td>
								<td width='13'><nobr><img src='$imageLoc/spacer.gif' width='2' height='17'>" . (($sortOnThis) ? ("<img src='$imageLoc/phAquaTable/" . (($order == 1) ? "ascending":"descending") . ".gif' width='9' height='17'>") :
												   (($sortOnThis2) ? ("<img src='$imageLoc/phAquaTable/unselected_" . (($order2 == 1) ? "ascending":"descending") . ".gif' width='9' height='17'>") : "")) . 
												   "<img src='$imageLoc/spacer.gif' width='2' height='17'></nobr></td>
							</tr>
						</table>";
					}
					else {
						$returnedText .= "<table border='0' cellpadding='0' cellspacing='0' width='100%'>
							<tr>
								<td width='8'><img src='$imageLoc/spacer.gif' width='8' height='8'></td>
								<td><nobr><span class='tableHeader'>". $this->columnTitles[$i] . "</span><nobr></td>
								<td></td>
								<td width='13'></td>
							</tr>
						</table>";
					}
					
					$returnedText .= "</td>";
				}
				
				// Start table column right border
				
				if ($sortOnThis) { $returnedText .= "<td width='1'><img src='$imageLoc/phAquaTable/border_$state.gif' width='1' height='17'></td>"; }
				else {
					if ($this->columnTitles[$i+1] != NULL && $i+1 == $this->selectedColumn) { $returnedText .= "<td width='1'><img src='$imageLoc/phAquaTable/border_selected.gif' width='1' height='17'></td>"; }
					else { $returnedText .= "<td width='1'><img src='$imageLoc/phAquaTable/border_unselected.gif' width='1' height='17'></td>"; }
				}
			}
			
			$returnedText .= "</tr><tr><td bgcolor='#C3C3C3' height='2'><img src='$imageLoc/spacer.gif' width='1' height='2'></td><td bgcolor='white' colspan='$this->innerColspan'></td><td bgcolor='#C3C3C3' height='2'><img src='$imageLoc/spacer.gif' width='1' height='2'></td></tr>";
		}
		else {
			$returnedText .= "<tr><td width='1' bgcolor='#C3C3C3'><img src='$imageLoc/spacer.gif' width='1' height='1'></td>";

			if (is_null($this->columnCount) || $this->columnCount == 0) { $this->setColumnTitles(array(" ")); }

			for ($i=0; $i < $this->columnCount; $i++) {
				$columnSize = NULL;
				
				if ($this->columnTitles[$i] == "ATAdd" || $this->columnTitles[$i] == "ATDelete" || $this->columnTitles[$i] == "ATEdit") { $columnSize = "20"; }
				else if (is_array($this->columnWidths) && is_numeric($this->columnWidths[$i])) { $columnSize = $this->columnWidths[$i]; }
			
				$returnedText .= "<td height='1'" . (($columnSize != NULL) ? " width='" . $columnSize . "'":"") . " bgcolor='#C3C3C3'><img src='$imageLoc/spacer.gif' height='1'". (($columnSize != NULL) ? " width='" . $columnSize . "'":" width='1'") . "></td><td width='1' bgcolor='#C3C3C3'><img src='$imageLoc/spacer.gif' width='1' height='1'></td>";
			}
		}
		
		// Start Content Rows
		
		if ($this->autoSort) { $this->sortContent(); }
		
		for ($i=0; $i < $this->rowCount; $i++) {
			if ($this->autoColor) {
				if ($this->content[$i]["colorTrigger"] != "AUTO") {
					if ($this->content[$i]["rowColor"]) { $rowColor = $this->content[$i][rowColor]; }
					else { $rowColor = ($this->content[$i]["colorTrigger"]) ? $this->rowColor:$this->alternateRowColor; }
				}
				else { $rowColor = (!is_int($i/2)) ? $this->rowColor:$this->alternateRowColor; }
			}
			else { $rowColor = ($this->content[$i]["colorTrigger"] || !is_int($i/2)) ? $this->rowColor:$this->alternateRowColor; }
			
			if ($this->content[$i] == "hr") {				
				$returnedText .= tabs(5) . "<tr>
						<td bgcolor='#C3C3C3'><img src='$imageLoc/spacer.gif' width='1' height='3'></td>
						<td colspan='$this->innerColspan' bgcolor='white'><img src='$imageLoc/spacer.gif' width='100' height='3'></td>
						<td bgcolor='#C3C3C3'><img src='$imageLoc/spacer.gif' width='1' height='3'></td>
					</tr>
					<tr><td colspan='$this->outerColspan' bgcolor='#C3C3C3'><img src='$imageLoc/spacer.gif' width='100' height='1'></td></tr>
					<tr>
						<td bgcolor='#C3C3C3'><img src='$imageLoc/spacer.gif' width='1' height='3'></td>
						<td colspan='$this->innerColspan' bgcolor='white'><img src='$imageLoc/spacer.gif' width='100' height='3'></td>
						<td bgcolor='#C3C3C3'><img src='$imageLoc/spacer.gif' width='1' height='3'></td>
					</tr>";
			}
			else {
				$returnedText .= "<tr bgcolor='$rowColor'><td bgcolor='#C3C3C3' width='1'><img src='$imageLoc/spacer.gif' width='1' height='17'></td>";
				
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
						$returnedText .= $this->ATButton("Add",$this->name,$this->content[$i]["row"]->id);
					}
					else if ($this->columnTitles[$a] == "ATDelete" && is_object($this->content[$i]["row"])) {
						$returnedText .= $this->ATButton("Delete",$this->name,$this->content[$i]["row"]->id);
					}
					else if ($this->columnTitles[$a] == "ATEdit" && is_object($this->content[$i]["row"])) {
						$returnedText .= $this->ATButton("Edit",$this->name,$this->content[$i]["row"]->id);
					}
					else if (!is_object($this->content[$i]["row"]) && is_array($this->content[$i]["row"][$a])) {
						$cellType = $this->content[$i]["row"][$a][0];
						
						if ($cellType == "popup") {
							$selectedPopup = $this->content[$i]["row"][$a][count($this->content[$i]["row"][$a]) - 1];
							$popupCount = count($this->content[$i]["row"][$a]) - 1;

							$returnedText .= "<td><select name='" . $this->content[$i]["row"][$a][1] . "'>\n"; // Print select name
											
							for($j=2;$j < $popupCount; $j++) {
								$returnedText .= "<option value='" . $this->content[$i]["row"][$a][$j] . "'" . (($selectedPopup == $this->content[$i]["row"][$a][$j]) ? " selected":"") . ">" . $this->content[$i]["row"][$a][$j] . "\n";
							}
							$returnedText .= "</select></td>\n";
						}
						else if ($cellType == "textfield") {
							$fieldWidth = ($this->content[$i]["row"][$a][3] == "" || $this->content[$i]["row"][$a][3] == NULL) ? 12:$this->content[$i]["row"][$a][3];
							$returnedText .= "<td><img src='$imageLoc/spacer.gif' width='4' height='8'><input type='textfield' name='" . $this->content[$i]["row"][$a][1] . "' size='$fieldWidth' value='" . $this->content[$i]["row"][$a][2] . "'></td>\n";
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
							$returnedText .= "<td><nobr><img src='$imageLoc/spacer.gif' width='4' height='8'><textarea name='" . $this->content[$i]["row"][$a][1] . "' rows='$height' cols='$width'>" . $this->content[$i]["row"][$a][3] . "</textarea></nobr></td>\n";
						}
						else if ($cellType == "checkbox") {
							$returnedText .= "<td width='19'><input type='checkbox' name='" . $this->content[$i]["row"][$a][1] . "'" . (($this->content[$i]["row"][$a][2] == "yes") ? " checked":"") . "></td>";
						}
						else if ($cellType == "image") {
							if ($this->content[$i]["row"][$a][1] != "") {
								if ($this->content[$i]["row"][$a][2] == "" || $this->content[$i]["row"][$a][2] == NULL) {
									$returnedText .= "<td align='center'><img src='$imageLoc/" . $this->content[$i]["row"][$a][1] . "' border='0'></td>";
								}
								else {
									$returnedText .= "<td align='center'><a href='" . $this->content[$i]["row"][$a][2] . "'><img src='$imageLoc/" . $this->content[$i]["row"][$a][1] . "' border='0'></a></td>";
								}
							}
						}
						else if ($cellType == "html") {
							if ($this->content[$i]["row"][$a][2] != "" && $this->content[$i]["row"][$a][2] != NULL) {
								$columns = ($this->content[$i]["row"][$a][2] * 2) - 1;
							}
							else { $columns = 1; }
							
							if ($this->content[$i]["row"][$a][1] != "") {
								$returnedText .= (($columns) ? ("<td colspan='$columns'>\n"):("<td>\n")) . $this->content[$i]["row"][$a][1] . "\n</td>\n"; 
							}
							if ($columns != 1) { $a = $a + ($this->content[$i]["row"][$a][2] - 1); }
						}
						else if ($cellType == "file") {
							if ($this->content[$i]["row"][$a][1] != "") {
								$returnedText .= "<td align='left'><input type='hidden' name='MAX_FILE_SIZE' value='30000000'><input type='file' name='" . $this->content[$i]["row"][$a][1] . "' size='40' style='margin:4px'></td>";
							}
						}
						else if ($cellType == "ATAdd") { $returnedText .= $this->ATButton("Add",$this->name,$this->content[$i]["row"][$a][1]); }
						else if ($cellType == "ATEdit") { $returnedText .= $this->ATButton("Edit",$this->name,$this->content[$i]["row"][$a][1]); }
						else if ($cellType == "ATDelete") { $returnedText .= $this->ATButton("Delete",$this->name,$this->content[$i]["row"][$a][1]); }
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
							
							if ($typeArray != NULL && $this->content[$i]["sortable"]) {
								if ($typeArray[type] == "bool") { $actionKey = ($returnedValue) ? 0:1; }
								else if ($typeArray[type] == "case") { $actionKey = $returnedValue; }
								else if ($typeArray[type] == "function") { $actionKey = $returnedValue; }
								else if ($typeArray[type] == "HTMLFunction") { $actionKey = $returnedValue; }
								else {
									// No other column types yet.
								}
								
								if (is_array($typeArray["actions"])) {								
									if (is_array($typeArray["actions"][$actionKey])) {
										if ($typeArray["actions"][$actionKey][type] == "image") { $tempColumnContent = "<img src='$imageLoc/" . $typeArray["actions"][$actionKey][value] . "'>"; }
										else if ($typeArray["actions"][$actionKey][type] == "text") { $tempColumnContent = $typeArray["actions"][$actionKey][value]; }
										else {
											// No type that I recognize.
										}
									}
								}
								else {
									if ($typeArray[type] == "function" || $typeArray[type] == "HTMLFunction") {
										$action = str_replace("%value%",$actionKey,$typeArray["actions"]);
										eval('$returnedValue = ' . $action . ';');
									}
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
										$columns = ($arg * 2) - 1;
										$jumpTo = $a + ($arg - 1);
									}
									else if (!$arg) {
										$columns = ($this->columnCount * 2) - 1;
										$jumpTo = $a + ($this->columnCount - 1);
									}
									else { $columns = 1; }
									
									$returnedText .= "<td colspan='$columns'>";
								}
								else { $returnedText .= "<td>"; } // Object rows display this.
								
								if ($typeArray[type] != "HTMLFunction") { $returnedText .= "<img src='$imageLoc/spacer.gif' width='8' height='8'>"; }
								
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
								
								if ($condition == "link") {
									$returnedText .= "<a href='$arg' class='defaultText' border='0'>$tempColumnContent</a>";
								}
								else if (!is_null($link) && !is_null($key) && $this->content[$i][sortable]) {
									$returnedText .= "<a href='" . str_replace("%linkKey%",$this->content[$i]["row"]->$key,$link) . "' class='defaultText' border='0'>" . $tempColumnContent . "</a>";
								}
								else { $returnedText .= "<span class='defaultText'>" . $tempColumnContent . "</span>"; }	
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
										
					if ($a < $this->columnCount - 1) { $returnedText .= "<td width='1'></td>\n"; }
				}
		
				$returnedText .= "<td width='1' bgcolor='#C3C3C3'><img src='$imageLoc/spacer.gif' width='1' height='17'></td></tr>\n";
			}
		}
		
		// End Content Rows and Close Table
		$returnedText .= "<tr><td colspan='$this->outerColspan' bgcolor='#C3C3C3'><img src='$imageLoc/spacer.gif' width='100' height='1'></td></tr></table>\n";
		
		if ($this->formAction) { $returnedText .= "</form>\n"; }
		
		if ($returnMode == "print") { print $returnedText; }
		else { return $returnedText; }
	}
}

?>