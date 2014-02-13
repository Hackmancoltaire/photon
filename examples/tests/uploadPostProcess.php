<?php

if ($_REQUEST["UUID"] != null) {
	print "alert('UUID: " . $_REQUEST["UUID"] . " uploaded successfully!');";
}
else {
	print "alert('There was a problem');";
}


?>
