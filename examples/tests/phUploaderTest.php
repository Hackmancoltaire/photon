<?php

require_once "siteConfig.php";

drawTopNavigation(null,array($partner->name,$appRoot . "/partners.php?action=editPartner&partnerID=" . $partner->id),true);


	$uploader = new phUploadTable;
	
	$uploader->setIconSize(32);
	$uploader->denyExtension("png");
	
	$uploader->setUploadCallback("uploadPostProcess.php?id=1");
	
	$uploader->display();

drawBottomNavigation(true);

?>