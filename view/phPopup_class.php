<?php

// Allow popup to accept results from a phSearch!!!




class phPopup {

	var $name;
	var $style;

	var $items;

	var $startItems;
	var $endItems;
	
	var $selectedItem;

	function phPopup($name=null) {
		if (is_null($name)) { $this->name = uniqid("phPopup-"); }
		else { $this->name = $name; }
	}
	
	function setItems($itemArray=null,$mode) {
		// Modes (numeric, string, keyed)
		
		if (is_null($itemArray)) { $this->items = array(); }
		else {
			$this->items = array();
		
			$itemCount = count($itemArray);
			
			for ($i=0; $i < $itemCount; $i++) {
				if ($mode == "numeric") { $this->items[] = array($itemArray[$i],$i); }
				else if ($mode == "string") { $this->items[] = array($itemArray[$i],$itemArray[$i]); }
				else if ($mode == "keyed") { $this->items[] = array($itemArray[$i]["name"],$itemArray[$i]["value"]); }
				else { }
			}
		}
	}
	
	function setStyle($style=null) {
		if (!is_null($syle)) { $this->style = $style; }
	}
	
	function setReturnMode($mode="numeric") { $this->returnMode = $mode; }
	
	function setStartItems($itemArray=null) {
		if (is_array($itemArray)) {
			if (!empty($itemArray["name"]) && !empty($itemArray["value"])) {
				$this->startItems = array($itemArray["name"],$itemArray["value"]);
			}
		}
	}
	
	function setEndItems($itemArray=null) {
		if (is_array($itemArray)) {
			if (!empty($itemArray["name"]) && !empty($itemArray["value"])) {
				$this->endItems = array($itemArray["name"],$itemArray["value"]);
			}
		}	
	}
	
	function setSelectedItem($item=null) { if (!is_null($item)) { $this->selectedItem = $item; } }
	
	function display($mode="print",$selectedItem=null) {
		if (!is_null($selectedItem)) { $this->selectedItem = $selectedItem; }
	
		$itemCount = count($this->items);	
		$returnedText = "";
		
		$returnedText .= "<select name='" . $this->name . "'" . (($this->style != null) ? " style='" . $this->style . "'":"") . ">\n";
			
		if (!is_null($this->startItems)) {
			if (($this->startItems[0] == $this->selectedItem) || ($this->startItems[1] == $this->selectedItem)) {
				$returnedText .= "<option value='" . (($mode == "string") ?$this->startItems[1]:$i) . "' selected>" . $this->startItems[0] . "\n";
			}
			else { $returnedText .= "<option value='" . (($mode == "string") ? $this->startItems[1]:$i) . "'>" . $this->startItems[0] . "\n"; }
		}
		else {
			if (is_null($selectedItem)) { $returnedText .= "<option value='null' selected>- Select -\n"; }
			else { $returnedText .= "<option value='null'>- Select -\n"; }
		}
		
		for ($i=0; $i < $itemCount; $i++) {
			if (($this->items[$i][0] == $this->selectedItem && $this->items[$i][0] === $this->selectedItem) || ($this->items[$i][1] == $this->selectedItem && $this->items[$i][1] === $this->selectedItem)) {
				$returnedText .= "<option value='" . $this->items[$i][1] . "' selected>" . $this->items[$i][0] . "\n";
			}
			else { $returnedText .= "<option value='" . $this->items[$i][1] . "'>" . $this->items[$i][0] . "\n"; }
		}
		
		if (!is_null($this->endItems)) {
			if (($this->endItems[0] == $this->selectedItem) || ($this->endItems[1] == $this->selectedItem)) {
				$returnedText .= "<option value='" . (($mode == "string") ?$this->endItems[1]:$i) . "' selected>" . $this->endItems[0] . "\n";
			}
			else { $returnedText .= "<option value='" . (($mode == "string") ? $this->endItems[1]:$i) . "'>" . $this->endItems[0] . "\n"; }
		}
		
		$returnedText .= "</select>\n";
		
		if ($mode == "print") { print $returnedText; }
		else { return $returnedText; }	
	}
}

?>