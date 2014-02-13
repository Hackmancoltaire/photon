<?php

// This is an alias of the phTabs class to ensure that older sites that have not
// upgraded their versions of PHOTON will not break. Please update your documents!

require_once("phTabs_class.php");

class phAquaTabs extends phTabs {
	function phAquaTable($tabName=NULL) {
		parent::phTabs($tabName);
	}
}

?>