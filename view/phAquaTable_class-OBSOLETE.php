<?php

// This is an alias of the phTable class to ensure that older sites that have not
// upgraded their versions of PHOTON will not break. Please update your documents!

require_once("phTable_class.php");

class phAquaTable extends phTable {
	function phAquaTable($tableName=NULL) {
		parent::phTable($tableName);
	}
}

?>