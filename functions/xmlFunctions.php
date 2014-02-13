<?php

// - PHOTON XML Functions file. This file sets up the XML parser in PHP to
// properly handle Mac OX X plist files and convert them into a usable PHP
// keyed array. The parser handles infinite depth and keyed as well as non
// keyed information. The parser also caches parsed files so that it is not
// required to read and parse them a second time during the execution.

function parseXMLFile($file) {
	if ($file) {
		$xml = new phXMLImporter($file);
		
		if ($xml->valid) { return $xml->getResults(); }
		else { return null; }
	}
	else { return NULL; }
}

function parseModuleInput($inputArray,$moduleType="mm") {
	$moduleID = $inputArray[moduleID];
	$parsedArray = $inputArray;
	
	$inModule = parseXMLFile(findLatest($moduleID,$moduleType,1));
	
	// Unset variables coming through that are not releated to saving data
	unset(
		$parsedArray[PHPSESSID],
		$parsedArray[web],
		$parsedArray[action],
		$parsedArray[moduleID],
		$parsedArray[instanceID],
		$parsedArray[specID],
		$parsedArray[note]		
	);
	
	$moduleDefaults = getModuleDefaults($inputArray[moduleID]);
	$finalArray = array_compare($parsedArray,$moduleDefaults);
	
	return $finalArray;
}

function cleanModuleInput($inputArray) {
	// Depreciated. Please use the new cleanRequest function.
	return cleanRequest($inputArray);
}

function array_compare($a,$b) {
	$bKeys = array_keys($b);
	$unfilteredKeys = array("userTarget","locationTarget"); // Keys that we let pass through
	$returnedArray = array();
	
	foreach($bKeys as $bKeyName) {
		if (!array_key_exists($bKeyName,$a)) { $a[$bKeyName] = "NO"; }
		if ($a[$bKeyName] != $b[$bKeyName]) { $returnedArray[$bKeyName] = $a[$bKeyName]; }
	}
	foreach($unfilteredKeys as $unFKey) {
		if (array_key_exists($unFKey,$a)) { $returnedArray[$unFKey] = $a[$unFKey]; }
	}
	
	return $returnedArray;
}

?>