<?php

class phSegmentedControl {
	
	var $name;					// String
	var $controls;				// Array
	var $selectedControl;		// Int
	var $disabledControls;		// Array
	var $size;					// String: regular | small | mini
	var $removeKeyArray;		// Array
	
	var $returnedText;			// Temporary Holder
	
	function phSegmentedControl($name=NULL) {
		$this->size = "regular";
		$this->controls = array();
		$this->disabledControls = array();
		$this->removeKeyArray = array();
		
		$this->name = (!$name) ? "genericControl":$name;
	}
	
	function setControls($array) { $this->controls = (is_array($array)) ? $array:NULL; }
	function setSelectedControl($index) { $this->selectedControl = (!$index) ? 0:$index; }
	function setSize($size = "regular") { $this->size = $size; }
	
	function setDisabledControls($disabledIndexArray=NULL) {
		$this->disabledControls = (is_array($disabledIndexArray)) ? $disabledIndexArray:array();
	}
	
	function setRemovedKeys($keys) { $this->removeKeyArray = (is_array($keys)) ? $keys:array(); }
	
	function createControls($controls,$selectedControl = -1,$disabledControls = array(),$alternateText = NULL) {		
		$controlCount = count($controls);
		
		if ($this->size == "regular") {
			$tabFontClass = "-PANTHER";
			
			$typeH = 24;
			$capW = 12;
			$capInnerW = 11;
			$capOuterW = 4;
			$spacerW = 11;
		}
		else if ($this->size == "small") {
			$tabFontClass = "-small-PANTHER";
			
			$typeH = 22;
			$capW = 8;
			$capInnerW = 9;
			$capOuterW = 5;
			$spacerW = 10;
		}
		else if ($this->size == "mini") {
			$tabFontClass = "-mini-PANTHER";
			
			$typeH = 21;
			$capW = 7;
			$capInnerW = 8;
			$capOuterW = 5;
			$spacerW = 8;
		}
		else { $tabFontClass = "-PANTHER";	}
	
		for ($i=0; $i < $controlCount; $i++) {
			$name = $controls[$i];
			$disabled = FALSE;
			
			if (in_array($i,$disabledControls)) {
				$state = $this->size . "_" . "disabled";
				$tabFont = "tabHeaderDisabled" . $tabFontClass;
				$disabled = TRUE;
			}
			else { 
				$state = $this->size . "_" . (($name == $controls[$selectedControl]) ? "selected":"unselected");
				$tabFont = "tabHeader" . $tabFontClass;
				$disabled = FALSE;
			}
			
			$state .= "_nobox";
						
			$this->returnedText .= tabs(8);
					
			if ($i == 0) { $this->returnedText .= "<td width='5'><img src='" . phImage("spacer.gif") . "' width='5' height='5'></td><td width='$capW'><img src='" . phImage("tabs-PANTHER/leftCap_$state.gif") . "' width='$capW' height='$typeH'></td>\n"; }
			else { $this->returnedText .= "<td width='1'><img src='" . phImage("tabs-PANTHER/seperator_$state.gif") . "' width='1' height='$typeH'></td>\n"; }	
			
			if ($i == 0) { $this->returnedText .= "<td width='$capOuterW'><img src='" . phImage("tabs-PANTHER/fill_$state.gif") . "' width='$capOuterW' height='$typeH'></td>"; }
			else { $this->returnedText .= "<td width='$spacerW'><img src='" . phImage("tabs-PANTHER/fill_$state.gif") . "' width='$spacerW' height='$typeH'></td>"; }
			
			if ($disabled) {
				$this->returnedText .= "<td background='" . phImage("tabs-PANTHER/fill_$state.gif") . "'><nobr><span class='tabHeaderDisabled-PANTHER'>$name</span></nobr></td>\n";
			}
			else {
				$this->returnedText .= "<td background='" . phImage("tabs-PANTHER/fill_$state.gif") . "'><nobr><a href='" . buildURL($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING'],array($this->name . (($alternateText) ? ("_" . $alternateText):"") . "_activeControl=$i"),$this->removeKeyArray) . "' class='$tabFont' onclick='this.blur()'>$name</a></nobr></td>\n";
			}
	
			if ($i == $controlCount -1) { $this->returnedText .= "<td width='$capOuterW'><img src='" . phImage("tabs-PANTHER/fill_$state.gif") . "' width='$capOuterW' height='$typeH'></td>"; }
			else { $this->returnedText .= "<td width='$spacerW'><img src='" . phImage("tabs-PANTHER/fill_$state.gif") . "' width='$spacerW' height='$typeH'></td>"; }
		
			if ($i == $controlCount -1) { $this->returnedText .= "<td width='$capW'><img src='" . phImage("tabs-PANTHER/rightCap_$state.gif") . "' width='$capW' height='$typeH'></td><td width='5'><img src='" . phImage("spacer.gif") . "' width='5' height='5'></td>\n"; }
		}
	}
	
	function display($returnMode = "print") {	
		$this->returnedText .= "<!-- Start Controls -->\n";
		$this->returnedText .= "\n" . tabs(3) ."<table border='0' cellpadding='0' cellspacing='0'><tr>\n";

		if (!$this->selectedControl || $this->selectedControl == NULL) { $currentNav = -1; }
		
		// Create our controls
		
		$this->createControls($this->controls,$this->selectedControl,$this->disabledControls);
		
		$this->returnedText .= tabs(7) . "</tr></table>";
		$this->returnedText .= "<!-- End Controls -->\n";
		
		if ($returnMode == "print") { print $this->returnedText; }
		else { return $this->returnedText; }
	}
}
	
?>