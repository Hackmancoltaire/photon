<?php

include "./common/magicDance.php";
include "./common/control/phSearch_class.php";
include "./common/view/phSearchTable_class.php";

print "<html>
<head>
	<title></title>
	<link rel='stylesheet' href='/hotads2k5/common/css.css'>
</head>";

$ad = new Ad;
$user = new User;

$mySearch = new phSearch;

// This will not filter out ANY fields.
$mySearch->addTargetObjectWithFilter($ad,array("headline","message","userEmail"));
//$mySearch->addTargetObjectWithFilter($user,array("email"));

$mySearchCriteria = array(
	terms => "apple.com",
	absolutes => array(
		"owner" => array("=",0),
		"projectStatus" => array("!=",0,"!=",5)
	)
);

$mySearch->search($mySearchCriteria);

// print_r($mySearch->getResults());

$searchTable = new phSearchTable("searchTable");

//$searchTable->setShowObjectHeaders(TRUE);
//$searchTable->setObjectHeaderTitle("ad","Ads");
//$searchTable->setObjectHeaderTitle("user","Users");
$searchTable->setShowRank(TRUE);
//$searchTable->setColumnIdentifiersForObject("ad",array("headline","modificationDate"));

$searchTable->setShowColumnHeaders(TRUE);
$searchTable->setColumnTitles(array("Headline","Category","Area","Post Date"));
$searchTable->setColumnIdentifiers(array("headline","category","area","creationDate"));
$searchTable->autoSort = TRUE;
$searchTable->sortOrder = (!$searchTable_sortOrder) ? "DESC":$searchTable_sortOrder;
$searchTable->selectedColumn = (!$searchTable_selectedColumn) ? 3:$searchTable_selectedColumn;
$searchTable->setLinkAndKeyForColumn("ad.php?adID=%linkKey%","id","headline");
//$searchTable->setColumnTypes(array(creationDate => array(type => "function", actions => "dateProcessor('%value%',FALSE)")));

//$searchTable->setShowObjectHeaders(true);

$searchTable->setColumnIdentifiersForObject("ad",array("headline","creationDate"));
//$searchTable->setColumnIdentifiersForObject("user",array("id","email"));

$searchTable->addResults($mySearch->getResults());

$searchTable->display();

?>