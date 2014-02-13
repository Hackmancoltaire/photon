<?php

class phTabs {
	
	// UI Element: Aqua Tabs - Panther
	// Supports tab switching and tab levels
	
	var $name;
	var $tabs;				// Array
	var $altTabs;			// Array
	var $altTabPosition; 	// String: left | right
	var $selectedTab;		// Int
	var $selectedAltTab;	// Int
	var $disabledTabs;		// Array
	var $disabledAltTabs;	// Array
	var $style;				// String: rounded | full
	var $tabSize;			// String: regular | small | mini
	var $contentArray;		// Array
	var $topDisplayed;		// Bool
	
	function phTabs($tabName=NULL) {
		$this->style = "rounded";
		$this->tabSize = "regular";
		$this->tabs = array();
		$this->altTabs = array();
		$this->altTabPosition = "left";
		$this->disabledTabs = array();
		$this->disabledAltTabs = array();
		$this->removeKeyArray = array();
		$this->contentArray = array();
		$this->topDisplayed = FALSE;
		
		$this->name = (!$tabName) ? "genericTabs":$tabName;
	}
	
	function setTabTitles($tabArray) { $this->tabs = (is_array($tabArray)) ? $tabArray:NULL; }

	function setAltTabTitles($tabArray) { $this->altTabs = (is_array($tabArray)) ? $tabArray:NULL; }

	function setSelectedTab($tabIndex) { $this->selectedTab = (!$tabIndex) ? 0:$tabIndex; }

	function setSelectedAltTab($tabIndex) { $this->selectedAltTab = (!$tabIndex) ? 0:$tabIndex;	}
	
	function setStyle($style = "rounded") { $this->style = $style; }
	
	function setTabSize($size = "regular") { $this->tabSize = $size; }
	
	function setDisabledTabs($disabledTabIndexArray=NULL) {
		$this->disabledTabs = (is_array($disabledTabIndexArray)) ? $disabledTabIndexArray:array();
	}
	
	function setAltDisabledTabs($disabledTabIndexArray=NULL) {
		$this->disabledAltTabs = (is_array($disabledTabIndexArray)) ? $disabledTabIndexArray:array();
	}
	
	function setDefaultViewFor($defaultViewKey,$defaultView) {
		// This allows you to set a list of php commands to be executed when a certain call is made. Saving some code space.
		if ($defaultView != NULL) { $this->contentArray[$defaultViewKey] = $defaultView; }
	}
	
	function defaultViewFor($defaultViewKey) {
		// Evauluate the code in a stored location in the content array
		if ($this->contentArray[$defaultViewKey] != NULL) { return $this->contentArray[$defaultViewKey]; }
	}
	
	function setRemovedKeys($removedKeys) { $this->removeKeyArray = (is_array($removedKeys)) ? $removedKeys:array(); }
	
	function createTabs($tabArray,$selectedTab = -1,$disabledTabs = array(),$alternateText = NULL) {
		// This is a temporary hack....do the right thing....Mr. President!
		$imageLoc = $GLOBALS["registeredLocations"]["Images"][0];
		
		$tabCount = count($tabArray);
		
		if ($this->tabSize == "regular") {
			$tabFontClass = "-PANTHER";
			
			$typeH = 24;
			$capW = 12;
			$capInnerW = 11;
			$capOuterW = 4;
			$spacerW = 11;
		}
		else if ($this->tabSize == "small") {
			$tabFontClass = "-small-PANTHER";
			
			$typeH = 22;
			$capW = 8;
			$capInnerW = 9;
			$capOuterW = 5;
			$spacerW = 10;
		}
		else if ($this->tabSize == "mini") {
			$tabFontClass = "-mini-PANTHER";
			
			$typeH = 21;
			$capW = 7;
			$capInnerW = 8;
			$capOuterW = 5;
			$spacerW = 8;
		}
		else { $tabFontClass = "-PANTHER";	}
	
		for ($i=0; $i < $tabCount; $i++) {
			$name = $tabArray[$i];
			$disabled = FALSE;
			
			if (in_array($i,$disabledTabs)) {
				$state = $this->tabSize . "_" . "disabled";
				$tabFont = "tabHeaderDisabled" . $tabFontClass;
				$disabled = TRUE;
			}
			else { 
				$state = $this->tabSize . "_" . (($name == $tabArray[$selectedTab]) ? "selected":"unselected");
				$tabFont = "tabHeader" . $tabFontClass;
				$disabled = FALSE;
			}
			
			if ($this->style == "nobox") { $state .= "_nobox"; }
						
			print tabs(8);
					
			if ($i == 0) { print "<td width='5'><img src='$imageLoc/spacer.gif' width='5' height='5'></td><td width='$capW'><img src='$imageLoc/tabs-PANTHER/leftCap_$state.gif' width='$capW' height='$typeH'></td>\n"; }
			else { print "<td width='1'><img src='$imageLoc/tabs-PANTHER/seperator_$state.gif' width='1' height='$typeH'></td>\n"; }	
			
			if ($i == 0) { print "<td width='$capOuterW'><img src='$imageLoc/tabs-PANTHER/fill_$state.gif' width='$capOuterW' height='$typeH'></td>"; }
			else { print "<td width='$spacerW'><img src='$imageLoc/tabs-PANTHER/fill_$state.gif' width='$spacerW' height='$typeH'></td>"; }
			
			if ($disabled) {
				print "<td background='$imageLoc/tabs-PANTHER/fill_$state.gif'><span class='tabHeaderDisabled-PANTHER'>$name</span></td>\n";
			}
			else {
				print "<td background='$imageLoc/tabs-PANTHER/fill_$state.gif'><a href='" . buildURL($_SERVER['PHP_SELF'],$_SERVER['QUERY_STRING'],array($this->name . (($alternateText) ? ("_" . $alternateText):"") . "_activeTab=$i"),$this->removeKeyArray) . "' class='$tabFont' onclick='this.blur()'>$name</a></td>\n";
			}
	
			if ($i == $tabCount -1) { print "<td width='$capOuterW'><img src='$imageLoc/tabs-PANTHER/fill_$state.gif' width='$capOuterW' height='$typeH'></td>"; }
			else { print "<td width='$spacerW'><img src='$imageLoc/tabs-PANTHER/fill_$state.gif' width='$spacerW' height='$typeH'></td>"; }
		
			if ($i == $tabCount -1) { print "<td width='$capW'><img src='$imageLoc/tabs-PANTHER/rightCap_$state.gif' width='$capW' height='$typeH'></td><td width='5'><img src='$imageLoc/spacer.gif' width='5' height='5'></td>\n"; }
		}
	}
	
	function display($section = NULL) {
		// This is a temporary hack....do the right thing....Mr. President!
		$imageLoc = $GLOBALS["registeredLocations"]["Images"][0];

		if ($section == "top") {
			$this->topDisplayed = TRUE;
		
			print "<!-- Start Tabs -->\n";

			if ($this->style == "rounded") {
				print "\n" . tabs(3) ."<table border='0' cellpadding='0' cellspacing='0' width='100%'>
				<tr>
					<td width='11'><img src='$imageLoc/tabs-PANTHER/boxUL.gif' width='11' height='24'></td>
					<td background='$imageLoc/tabs-PANTHER/boxTop.gif' align='center' valign='top'>
						<table border='0' cellpadding='0' cellspacing='0'>
							<tr>\n";
			}
			else if ($this->style == "full") {
				print "\n" . tabs(3) ."<table border='0' cellpadding='0' cellspacing='0' width='100%'>
				<tr>
					<td width='11'><img src='$imageLoc/tabs-PANTHER/boxTop.gif' width='11' height='24'></td>
					<td background='$imageLoc/tabs-PANTHER/boxTop.gif' align='center' valign='top'>
						<table border='0' cellpadding='0' cellspacing='0'>
							<tr>\n";
			}
			else if ($this->style == "nobox") {
				print "\n" . tabs(3) ."<table border='0' cellpadding='0' cellspacing='0' width='100%'>
				<tr>
					<td width='11'><img src='$imageLoc/spacer.gif' width='11' height='24'></td>
					<td align='center' valign='top'>
						<table border='0' cellpadding='0' cellspacing='0'>
							<tr>\n";
			}
			else {
				// An alternate style was provided and I don't know what to do. :(
			}
			
			if ($this->style != "tabless") {
				if (!$this->selectedTab || $this->selectedTab == NULL) { $currentNav = -1; }
				if (!$this->selectedAltTab || $this->selectedAltTab == NULL) { $currentAltNav = -1; } 
			
				// If there is an alternate tab set to display and it's position is left.
				
				if ($this->altTabs && $this->altTabPosition == "left") {
					$this->createTabs($this->altTabs,$this->selectedAltTab,$this->disabledAltTabs,"altTab");
				}
				
				// Create our standard tabs.
				
				$this->createTabs($this->tabs,$this->selectedTab,$this->disabledTabs);
				
				// If there is an alternate tab set to display and it's position is left.
				
				if ($this->altTabs && $this->altTabPosition == "right") {
					$this->createTabs($this->altTabs,$this->selectedAltTab,$this->disabledAltTabs,"altTab");
				}
				
				if ($this->style == "rounded") {		
					print tabs(7) . "</tr>
							</table>
						</td>
						<td width='11'><img src='$imageLoc/tabs-PANTHER/boxUR.gif' width='11' height='24'></td>
					</tr>
					<tr>
						<td background='$imageLoc/tabs-PANTHER/boxL.gif'><img src='$imageLoc/spacer.gif' width='11' height='11'></td>
						<td bgcolor='#EFEFEF'><img src='$imageLoc/spacer.gif' width='10' height='10'><br>\n";	
				}
				else if ($this->style == "full") {
					print tabs(7) . "</tr>
						</table>
					</td>
					<td width='11'><img src='$imageLoc/tabs-PANTHER/boxTop.gif' width='11' height='24'></td>
				</tr>
				<tr>
					<td bgcolor='#EFEFEF'><img src='$imageLoc/spacer.gif' width='11' height='11'></td>
					<td bgcolor='#EFEFEF'><img src='$imageLoc/spacer.gif' width='10' height='10'>\n";
				}
				else if ($this->style == "nobox") {
					print tabs(7) . "</tr>
						</table>
					</td>
					<td width='11'><img src='$imageLoc/spacer.gif' width='11' height='24'></td>
				</tr>
				<tr>
					<td><img src='$imageLoc/spacer.gif' width='11' height='11'></td>
					<td><img src='$imageLoc/spacer.gif' width='10' height='10'>\n";
				}
			}
		}
		else if ($section == "bottom" && $this->topDisplayed) {			
			if ($this->style == "rounded") {
				print tabs(6) . "</td>
				<td background='$imageLoc/tabs-PANTHER/boxR.gif'><img src='$imageLoc/spacer.gif' width='11' height='11'></td>
					</tr>
					<tr>
						<td width='11'><img src='$imageLoc/tabs-PANTHER/boxBL.gif' width='11' height='11'></td>
						<td background='$imageLoc/tabs-PANTHER/boxBottom.gif'><img src='$imageLoc/spacer.gif' width='11' height='11'></td>
						<td width='11'><img src='$imageLoc/tabs-PANTHER/boxBR.gif' width='11' height='11'></td>
					</tr>
				</table>";
			}
			else if ($this->style == "full") {
				print tabs(6) . "</td>
				<td bgcolor='#EFEFEF'><img src='$imageLoc/spacer.gif' width='11' height='11'></td>
					</tr>
					<tr>
						<td width='11'><img src='$imageLoc/tabs-PANTHER/boxBottom.gif' width='11' height='11'></td>
						<td background='$imageLoc/tabs-PANTHER/boxBottom.gif'><img src='$imageLoc/spacer.gif' width='11' height='11'></td>
						<td width='11'><img src='$imageLoc/tabs-PANTHER/boxBottom.gif' width='11' height='11'></td>
					</tr>
				</table>";
			}
			else if ($this->style == "nobox") {
				print tabs(6) . "</td>
				<td><img src='$imageLoc/spacer.gif' width='11' height='11'></td>
					</tr>
				</table>";
			}
		}
		else if ($section == "bottom-flat") {
			print tabs(6) . "</td>
				<td bgcolor='#EFEFEF'><img src='$imageLoc/spacer.gif' width='11' height='11'></td>
					</tr>
				</table>";
		}
		else { print "<!-- No display calls were made -->"; }
		print "<!-- End Tabs -->\n";
	}
}
	
?>