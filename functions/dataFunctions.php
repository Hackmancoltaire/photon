<?php

function validateInput($input) {
	// This function does valididty checks on an array or an individual input variable.
	// Valid variables are greater then Zero and a numeric variable.
	// This check is mainly used to ensure that a list of ids are proper for MySQL.
	
	if (is_array($input)) {
		$returnedValue = true;
		$inputCount = count($input);
		
		for ($i=0; $i < $inputCount; $i++) {
			if (!(is_numeric($input[$i]) && $input[$i] > 0)) { $returnedValue = false; }
		}
		return $returnedValue;
	}
	else { return (is_numeric($input) && $input > 0) ? true : false; }
}

function buildURL($initialURL,$queryString,$addedElements,$removedElements=NULL) {
	// This function takes a URL and an initial query string and adds or removes
	// elements from the URL and returns a resulting url that does not duplicate
	// any of the added elements OR contain any of the removed elements.

	if (!$initialURL) {	return null; }
	else {
		if ($queryString) {
			$tempQueryArgs = explode("&",$queryString);
		}
		$queryArgs = array();
		$tempQueryArgsCount = count($tempQueryArgs);
		
		for ($i=0; $i < $tempQueryArgsCount; $i++) {
			list($tempName,$tempValue) = explode("=",$tempQueryArgs[$i]);
			$queryArgs[$tempName] = $tempValue;
		}
		
		if ($addedElements) {
			$addedElementsCount = count($addedElements);
		
			for ($i=0; $i < $addedElementsCount; $i++) {
				list($addedName,$addedValue) = explode("=",$addedElements[$i]);
				$queryArgs[$addedName] = $addedValue;
			}
		}
		
		if ($removedElements) {
			$removedElementsCount = count($removedElements);
		
			for ($i=0; $i < $removedElementsCount; $i++) { unset($queryArgs[$removedElements[$i]]); }
		}
		
		$queryArgsKeys = array_keys($queryArgs);
		$tempQueryArgs = null; $tempQueryArgs = array();
		$queryArgsCount = count($queryArgs);
		
		for ($i=0; $i < $queryArgsCount; $i++) {
			$tempName = $queryArgsKeys[$i];
			$tempValue = $queryArgs[$queryArgsKeys[$i]];
			array_push($tempQueryArgs,"$tempName=$tempValue");
		}
		
		$url = $initialURL . "?" . implode("&",$tempQueryArgs);
		
		return $url;
	}
}

function dateProcessor($timestamp = NULL,$requiresConversion = TRUE) {
	// Converts a standard MySQL timestamp into a string containing the date and
	// time. Passing $requiresConversion as TRUE will process any older style date
	// like 2006-12-01 into its timestamp equivilant.

	if ($timestamp) {
		if ($requiresConversion) { $convertedTimestamp = convert_time($timestamp); }
		else { $convertedTimestamp = $timestamp; }
		
		$id = getDate($convertedTimestamp); // id = Input Date
		$nd = getDate();
		$outputDate = "";
		
		if ($id[mon] == $nd[mon]) {
			if ($id[mday] == $nd[mday]) { $outputDate = "Today"; }				// Today
			else if ($id[mday] == $nd[mday]-1) { $outputDate = "Yesterday"; }	// Yesterday
			else { $outputDate = date("n/j/y",$convertedTimestamp); }			// 1/15/2003
		}
		else { $outputDate = date("n/j/y",$convertedTimestamp); }				// 1/15/2003
	
		if ($id[hours] != 0 && $id[minutes] != 0) {
			$outputDate .= " at " . date("g:i A",$convertedTimestamp);					// at 1:12 AM
		}
		
		return $outputDate;
	}
	else { return NULL; }
}

function convert_time($mysql_timestamp) { 
	// Converts a MySQL timestmap into a mktime array for use by dateProcessor
	// YYYYMMDDHHMMSS
	if (ereg("^([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})",$mysql_timestamp,$res)) {		
		$year=$res[1]; 
		$month=$res[2]; 
		$day=$res[3]; 
		$hour=$res[4]; 
		$min=$res[5]; 
		$sec=$res[6]; 
		
		return mktime($hour,$min,$sec,$month,$day,$year);
	}
	else { return NULL;	}
}

function isEmail($emailAddress) {
	// Checks to see if provided address is in the proper format for an email address.
	if (ereg( '^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+'. '@'. '[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.' . '[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$', $emailAddress) == 1) {
		return true;
	}
	else { return false; }
}

function bytesToHumanReadableUsage($bytes, $precision = 2, $names = '') {
	if (!is_numeric($bytes) || $bytes < 0) { return false; }
	
	for ($level = 0; $bytes >= 1024; $level++) { $bytes /= 1024; }

	switch ($level) {
		case 0:
			$suffix = (isset($names[0])) ? $names[0] : 'Bytes'; break;
		case 1:
			$suffix = (isset($names[1])) ? $names[1] : 'KB'; break;
		case 2:
			$suffix = (isset($names[2])) ? $names[2] : 'MB'; break;
		case 3:
			$suffix = (isset($names[3])) ? $names[3] : 'GB'; break;
		case 4:
			$suffix = (isset($names[4])) ? $names[4] : 'TB'; break;
		default:
			$suffix = (isset($names[$level])) ? $names[$level] : ''; break;
	}

	if (empty($suffix)) {
		trigger_error('Unable to find suffix for case ' . $level);
		return false;
	}

	return round($bytes, $precision) . ' ' . $suffix;
}

function stringToHTML($text,$addBRs = TRUE,$stripNL = FALSE) {
	// Converts any single quote chracters into their HTML equivilants and may convert
	// newlines into <BR> tags.

	$string = str_replace("\"","&quot;",$text);
	$string = str_replace("'","&#039;",$string);
	
	if ($addBRs) { $string = str_replace("\n","<br>",$string); }
	if ($stripNL) { $string = str_replace("\n","",$string); }
	
	return $string;
}

function stringToLinkedString($str) {
	// Finds any http or email addresses and converts them into HTML links

	$str = preg_replace('#((\w*)\@(\w*)\.(\w*))[^\s<]*#','<a href="mailto:\\1">\\1</a>',$str);
	$str = preg_replace('#(http://)([^\s<]*)#', '<a href="\\1\\2" target="_new">\\1\\2</a>', $str);
	return $str;
}

function getmicrotime() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

function canPassThru($code) {	
	// This is merely a check function. Check that the passthrucode has been supplied and was correct.
	// This only circumvents security if the session was not registered which in some cases needs to happen due to bugs in PHP.
	// One code to rule them all!
	// Security Note: If you use this function to bypass a portion of your security you'll want to use your own code!
	
	if ($code == "1c0d32rul37h3m4ll") { return TRUE; }
	else { return FALSE; }
}

function validateImageURL($URL = NULL) {
	// Checks that an image URL actually exists by calling the host.

	if ($URL) {
		$urlInfo = parse_URL($URL);
				
		if ($urlInfo[scheme] == "http") {
			$imageInfo = getimagesize($URL);
			
			if ($imageInfo) {
				// Image is invalid
				if ($imageInfo[2] >= 1 && $imageInfo[2] <= 3) { return TRUE; }
				else { return FALSE; }
			}
			else { return FALSE; } // Image is invalid
		}
		else { return FALSE; } // Schema is invalid
	}
	else { return FALSE; } // No URL was provided
}

?>
