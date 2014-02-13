<?php

	require_once "siteConfig.php";
	drawTopNavigation();
	
	print "<form method='POST'><input type='text' placeholder='Search Criteria' value='" . $_REQUEST["searchCriteria"] . "' name='searchCriteria'><input type='submit'></form><br>";

	if (!is_null($_REQUEST["searchCriteria"])) {
		$project = new Project;
//		$build = new Build;
//		$specification = new Specification;

		$mySearch = new phSearch;
		
		$mySearch->addTargetObjectWithFilter($project,array("name","description"));
//		$mySearch->addTargetObjectWithFilter($build,array("productFamily","marketingPart","notes"));
//		$mySearch->addTargetObjectWithFilter($specification,array("name","description"));
		
		$mySearchCriteria = array(
			"terms" => $_REQUEST["searchCriteria"]
		);
		
		$searchTable = new phSearchTable("searchTable");
		
		// ------- SPOTLIGHT MODE -------		
			$searchTable->setDisplayMode("Spotlight");
	
			$searchTable->setObjectHeaderTitle("Project","Projects");
			$searchTable->setObjectHeaderTitle("Specification","Specifications");
	
			//$searchTable->setShowRank(FALSE); // Can be used in either mode
		
			$searchTable->setAutoSort(TRUE);
			$searchTable->setSelectedColumn(2,"DESC");
			$searchTable->setColumnIdentifiersForObject("Project",array("name","modificationDate"));
			//$searchTable->setColumnIdentifiersForObject("Specification",array("name","modificationDate"));
	
			$searchTable->setColumnTypes(array(
				"date" => array(
					type => "function",
					actions => "dateProcessor('%value%',FALSE)"
				)
			));
			
		// --------- END ---------------

		$searchTable->setLinkAndKeyForObject("viewProject.php?projectID=%linkKey%","id","Project");

		$mySearch->search($mySearchCriteria);

		if ($mySearch->resultCount() > 0) { $searchTable->addResults($mySearch->getResults()); }
		else { $searchTable->addRow(array("There are no items that match your search.%span")); }

		$searchTable->display();
	}

	drawBottomNavigation();
?>