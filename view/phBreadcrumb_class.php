<?php

class phBreadcrumb {

	var $name;
	var $crumbs;
	
	var $displayCache;
	
	function phBreadcrumb($name=null) {
		$this->style = "graphical";
		$this->crumbs = array();
		$this->displayCache = null;

		$this->name = (!$name) ? "genericBreadcrumb":$name;
	}
	
	function initFromSession() {
		if (!empty($_SESSION[$this->name])) {
			$this->setCrumbs($_SESSION[$this->name]);
		}
	}
	
	function setStyle($style="graphical") {
		if ($style == "graphical" || $style == "text") {
			$this->style = $style;
		}
		else { $this->style = $style; }
	}
	
	function setCrumbs($array) {
		$this->crumbs = (is_array($array)) ? $array:NULL;
	}
	
	function addCrumb($name,$url) {
		$this->crumbs[] = $name;
		$this->crumbs[] = $url;
	}
	
	function removeAllCrumbs() {
		$this->crumbs = NULL;
		$this->displayCache = null;
	}
	
	function setCurrentCrumb($name,$location) {
		// Crumb would be an array with the name and url
		
		$nameIndex = array_search($name,$this->crumbs);
		$locationIndex = array_search($location,$this->crumbs);
		
		if (empty($nameIndex) && empty($locationIndex)) {
			$this->addCrumb($name,$location);
		}
		else if ($locationIndex == ($nameIndex + 1)) {
			$this->crumbs = array_slice($this->crumbs,0,$locationIndex+1);
		}
		else {
			// Non matching indexes. Don't cut crumbline.
		}
	}
	
	function display($returnMode="print") {
		// Save current crumbs
		if ($_SESSION['loggedIn']) {
			session_start();
			$_SESSION[$this->name] = $this->crumbs;
		}
	
		if (is_null($this->displayCache)) {
			$crumbCount = count($this->crumbs);

			if ($crumbCount > 0) {
				$this->displayCache .= "<!-- Start phBreadcrumb: " . $this->name . " -->\n<div class='phBreadcrumbs' id='container'>";
			
				for ($i=0; $i < $crumbCount; $i+=2) {
					if ($i==0) {
						if ($crumbCount < 4) {
							// Only the home link. Draw it.
							$this->displayCache .= "<a href='" . $this->crumbs[1] . "' class='phBreadcrumbs' id='home'><img src='" . phImage("phBreadCrumbs/crumbHomeTop.png") . "' border='0'></a>\n";
						}
						else {
							// At least one more then the home link. Draw the uncapped home.
							$this->displayCache .= "<a href='" . $this->crumbs[1] . "' class='phBreadcrumbs' id='home'><img src='" . phImage("phBreadCrumbs/crumbHomeCap.png") . "' border='0'><img src='" . phImage("phBreadCrumbs/crumbFill.png") . "' border='0' width='3' height='20'></a>\n";
						}
					}
					else {
						$this->displayCache .= "<div id='seperator' class='phBreadcrumbs'></div>\n";
						
						if ($i == ($crumbCount - 2)) {
							// Draw a normal crumb.	
							$this->displayCache .= "<a id='currentLocation' href='" . $this->crumbs[$i+1] . "' class='phBreadcrumbs'>" . $this->crumbs[$i] . "</a>\n";
							$this->displayCache .= "<div id='end' class='phBreadcrumbs'></div>\n";
						}
						else {
							$this->displayCache .= "<a href='" . $this->crumbs[$i+1] . "' class='phBreadcrumbs'>" . $this->crumbs[$i] . "</a>\n";
						}
					}
				}
	
				$this->displayCache .= "</div><div class='phClearer'></div>\n<!-- End phBreadcrumb: " . $this->name . " -->\n";
			}
		}

		if (!is_null($this->displayCache)) {
			if ($returnMode == "print") { print $this->displayCache; }
			else { return $this->displayCache; }
		}
	}
}

?>