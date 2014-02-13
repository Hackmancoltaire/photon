<?php

// - PHOTON FileSystem Functions. These functions deal with managing data from the file system.

function directoryListing($location) {
	$listingArray = array();
	
	if (!$location) { return NULL; }
	else {
		if ($handle = opendir($location)) {
			while (false !== ($file = readdir($handle))) { 
				if ($file != "." && $file != ".." && $file != ".DS_Store") {
					array_push($listingArray,$file);
				}
			}
			closedir($handle);
			usort($listingArray,"numericFileCompare");
			return $listingArray;
		}
		else { return NULL;	}
	}
}

function numericFileCompare($a, $b) {
	// Strips the extension off a file and then compares the dashed numerics of a file.
	// to be sure they are ordered. Intended for files like 12-1.ccm,12-100.ccm,14-1.ccm.

    if ($a == $b) { return 0; }
    else {
    	list($aA) = explode(".",$a);
    	list($bB) = explode(".",$b);
    	
    	if (strpos($aA,"-") === FALSE && strpos($bB,"-") === FALSE) {
    		$idA = $aA;
    		$idB = $bB;
    	}
    	else {
			list($idA,$versA) = explode("-",$aA);
			list($idB,$versB) = explode("-",$bB);
		}
	    
	   	if ($idA == $idB) {
	   		if ($versA == $versB) { return 0; }
	   		else if ($versA > $versB) { return 1; }
	   		else { return -1; }
	   	}
	   	else {
	   		return ($idA > $idB) ? 1 : -1;
	   	}
	}
} 

?>