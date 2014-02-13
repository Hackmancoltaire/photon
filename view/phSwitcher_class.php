<?php

/*

$mySwitcher->setItems(array(
	array("Name","URL"),
	array("Name","URL"),
	array("Name","URL")
));

<div name='mainScope' onMouseOver='scopeActive(this)' onMouseOut='scopeInactive(this)' onClick='scopeClick(this)' class='scope_inActive'>
	<div id='left'></div><a href="javascript:gotoURL('')" id='middle' onClick='this.blur()'>All</a><div id='right'></div>
</div>

*/


class phSwitcher {
	
	var $name;
	var $items;

	function phSwitcher($name=null) {
		if (is_null($name)) { $this->name = uniqid("phSwitcher-"); }
		else { $this->name = $name; }
	}
	
	function setItems($itemArray) {
	
	}
	
	function setSelectedItem() {
	
	}
	
	function display($returnMode) {
	
	}
}

?>