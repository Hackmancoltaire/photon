<?php

require_once "siteConfig.php";

//authenticateUser($_REQUEST["username"],$_REQUEST["password"]);

drawTopNavigation();

$myTabs = new phTabs("Test");

$myTabs->setTabTitles(array("Name","Address","Email","something","Email"));
$myTabs->setSelectedTab(3);
$myTabs->setDisabledTabs(array(0,1,2));

$myTabs->display("top");

print "asdf";

$myTabs->display("bottom");

drawBottomNavigation();

?>