<?php

function printPopupItems($itemArray,$selectedItem = NULL,$mode = "string",$extras = NULL,$returnMode = "print") {
	$returnedText = "";
		
	if ($extras && $extras[start]) { $returnedText .= "<option value='" . $extras[start][0] . "'" . (($selectedID == $extras[start][0]) ? "selected":"") . ">" . $extras[start][1] . "\n"; }
	else {
		if (!$selectedItem) { $returnedText .= "<option value='null' selected>- Select -\n"; }
		else { $returnedText .= "<option value='null'>- Select -\n"; }
	}
	
	$arrayKeys = array_keys($itemArray);
	$keyCount = count($arrayKeys);
	
	for ($i=0; $i < $keyCount; $i++) {
		if ($itemArray[$arrayKeys[$i]]) {
			if ($mode == "string") { $returnedText .= "<option value='" . $itemArray[$arrayKeys[$i]] . "'" . (($itemArray[$arrayKeys[$i]] == $selectedItem) ? " selected>":">") . $itemArray[$arrayKeys[$i]] . "\n"; }
			else if ($mode == "key") { $returnedText .= "<option value='" . $arrayKeys[$i] . "'" . (($arrayKeys[$i] == $selectedItem) ? " selected>":">") . $itemArray[$arrayKeys[$i]] . "\n"; }
			else if ($mode == "int") { $returnedText .= "<option value='$i'" . (($i == $selectedItem) ? " selected>":">") . $itemArray[$arrayKeys[$i]] . "\n"; }
			else { }
		}
	}
	
	if ($extras && $extras[end]) { $returnedText .= "<option value='" . $extras[end][0] . "'" . (($selectedID == $extras[end][0]) ? "selected":"") . ">" . $extras[end][1] . "\n"; }
	
	if ($returnMode == "print") { print $returnedText; }
	else { return $returnedText; }
}

function printKeyedPopupItems($itemArray,$selectedItem='-999',$allowNull=false,$returnMode="print") {
	// Depreciated. Convert to using printPopupItems in "key" mode.
	
	$returnedText = "";

	if ($allowNull) {
		if (!$selectedItem) { 
			$returnedText .= "<option value='null'".(($selectedItem==NULL)?" selected":"").">- Select -\n";
		}
	}
	foreach ($itemArray as $key => $value) {
		$returnedText .= "<option value='$key'".(($selectedItem==$key)?" selected":"").">$value\n";
	}
	
	if ($returnMode == "print") { print $returnedText; }
	else { return $returnedText; }
}

function closeWindow($reload) {
	// Useful if you want a reloaded window to close itself and reload the opening window.

	print "<html>
	<SCRIPT type='text/javascript' language='Javascript'>
	" . (($reload) ? "opener.location.reload();":"") . "
	self.close();
	</SCRIPT>
	</html>";
}

function tabs($number) {
	// This function is merely cosmetic. If you want code to maintain its readability when
	// it is spit out of the application you might want to add some tabs in there.

	$tabs = "";
	for ($i=0; $i < $number; $i++) { $tabs .= "\t"; }
	return $tabs;
}

function redirectToPage($page) {
	print "<script type='text/javascript' language='Javascript'>
		window.location.href = \"http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/". $page . "\";\n
	</script>";
}

function createShadowedBox($titleLinkArray) {
	global $imageLoc;
	
print "<table width='98%' border='0' cellpadding='0' cellspacing='0'>
	<tr>
		<td width='12' valign='bottom'><img src='$imageLoc/shadowedLines/tl.gif' width='12' height='11'></td>\n";
		
if (!$titleLinkArray) {
	print "<td background='$imageLoc/shadowedLines/t.gif'><img src='$imageLoc/spacer.gif' height='11' width='11'></td>\n";
}
else {
	print "<td>
			<!-- Title table -->
			<table width='100%' border='0' cellpadding='0' cellspacing='0'>
				<tr>
					<td></td>
					<td width='12' valign='bottom'><img src='$imageLoc/shadowedLines/tl.gif' width='12' height='11'></td>
					<td background='$imageLoc/shadowedLines/t.gif'><img src='$imageLoc/shadowedLines/t.gif' width='12' height='11'></td>
					<td width='12' valign='bottom'><img src='$imageLoc/shadowedLines/tr.gif' width='12' height='11'></td>
					<td></td>
				</tr>
				<tr>
					<td></td>
					<td width='12' background='$imageLoc/shadowedLines/l.gif'><img src='$imageLoc/shadowedLines/l.gif' width='12' height='11'></td>
					<td bgcolor='white'><span class='defaultText'><nobr><img src='$imageLoc/spacer.gif' width='5' height='9'>\n";

	for($i=0; $i < count($titleLinkArray); $i++) {
		$link = $titleLinkArray[$i][1];
		$title = $titleLinkArray[$i][0];
		
		print "<a href='$link'>$title</a>";
		
		if ($i < (count($titleLinkArray) - 1)) { print " >> "; }
	}
					
	print "<img src='$imageLoc/spacer.gif' width='5' height='9'></nobr></td>
					<td width='12' background='$imageLoc/shadowedLines/r.gif'><img src='$imageLoc/shadowedLines/r.gif' width='12' height='11'></td>
					<td></td>
				</tr>
				<tr>
					<td width='12' height='11'><img src='$imageLoc/shadowedLines/t.gif' width='12' height='11'></td>
					<td width='12' height='11'><img src='$imageLoc/shadowedLines/rc.gif' width='12' height='11'></td>
					<td bgcolor='white'><img src='$imageLoc/spacer.gif' width='10' height='11'></td>
					<td width='12' height='11'><img src='$imageLoc/shadowedLines/lc.gif' width='12' height='11'></td>
					<td width='100%' background='$imageLoc/shadowedLines/t.gif' valign='bottom'><img src='$imageLoc/shadowedLines/t.gif' width='12' height='11'></td>
				</tr>
			</table>
		</td>\n";
}

print "<td width='12' valign='bottom'><img src='$imageLoc/shadowedLines/tr.gif' width='12' height='11'></td>
	</tr>
	<tr>
		<td width='12' background='$imageLoc/shadowedLines/l.gif'><img src='$imageLoc/shadowedLines/l.gif' width='12' height='11'></td>
		<td>";		
}

function endShadowedBox() {
	global $imageLoc;
	
	print "</td>
		<td width='12' background='$imageLoc/shadowedLines/r.gif'><img src='$imageLoc/shadowedLines/r.gif' width='12' height='11'></td>
	</tr>
	<tr>
		<td width='12' height='11'><img src='$imageLoc/shadowedLines/bl.gif' width='12' height='11'></td>
		<td background='$imageLoc/shadowedLines/b.gif'><img src='$imageLoc/shadowedLines/b.gif' width='12' height='11'></td>
		<td width='12' height='11'><img src='$imageLoc/shadowedLines/br.gif' width='12' height='11'></td>
	</tr>
</table>
</center>";

}

function processCSSFile($inputFile) {
	// Takes a CSS file and converts any %image% links into their appropriate location.
	
	if ($inputFile) {	
		$newContent = "<style type='text/css'>\n";
		$cssFile = file_get_contents($GLOBALS["appServerRoot"] . $inputFile);
		
		if ($cssFile != null) {
		
			$newContent .= preg_replace("/\\%([a-zA-Z0-9\.\-\_\/]+)\\%/e", "phImage(\"\\1\")",$cssFile);
			$newContent .= "</style>";

			return $newContent;
		}
		else { return null; }
	}
	else { return null; }
}

function phImage($imageName,$local = false) {
	// Checks with the system cache of registered locations for an image and returns the path
	// to that image. This is useful if you don't want to have to worry about entering URLs
	// for images everywhere AND if you intend to mirgrate your application to other machines.

	global $appRoot;
	
	$found = false;
	
	if (array_key_exists("ImageCache",$GLOBALS["registeredLocations"])) {
		if (array_key_exists($imageName,$GLOBALS["registeredLocations"]["ImagesCache"])) {
			$found = true;
			return $GLOBALS["registeredLocations"]["ImagesCache"][$imageName];
		}
	}
	
	if (!$found) {	
		$locationCount = count($GLOBALS["registeredLocations"]["Images"]);
		
		for ($i=0; $i < $locationCount; $i++) {
			if (file_exists($GLOBALS["appServerRoot"] . "/" . $GLOBALS["registeredLocations"]["Images"][$i] . "/" . $imageName)) {
				if (array_key_exists("ImagesCache",$GLOBALS["registeredLocations"])) {
					$GLOBALS["registeredLocations"]["ImagesCache"][$imageName] = $GLOBALS["hostRoot"] . $appRoot . "/" . $GLOBALS["registeredLocations"]["Images"][$i] . "/" .  $imageName;
				}
				else {
					$GLOBALS["registeredLocations"]["ImagesCache"] = array();
					$GLOBALS["registeredLocations"]["ImagesCache"][$imageName] = $GLOBALS["hostRoot"] . $appRoot . "/" . $GLOBALS["registeredLocations"]["Images"][$i] . "/" .  $imageName;
				}
				
				$found = true;
				return $GLOBALS["hostRoot"] . $appRoot . "/" . $GLOBALS["registeredLocations"]["Images"][$i] . "/" .  $imageName;
			}
		}
		
		// If cannot find image.
		if (!$found) { return "null.gif"; }
	}
}

function cleanRequest($inputArray) {
	// Use this function to clean up text coming from forms. Escpecially unicode and UTF-8 encoded text.

	$convmap = array(0xFF, 0x2FFFF, 0, 0xFFFF);
	$aKeys = array_keys($inputArray);
	$aKeysCount = count($aKeys);
	
	for($i=0; $i < $aKeysCount; $i++) {
		$inputArray[$aKeys[$i]] = mb_decode_numericentity(urldecode(stripSlashes($inputArray[$aKeys[$i]])),$convmap,"UTF-8");
	}
	
	return $inputArray;
}

function phIsNull($value) {
	if (is_null($value) || $value == "null" || $value == "") { return true; }
	else { return false; }
}




?>
