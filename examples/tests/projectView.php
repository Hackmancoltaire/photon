<?php

	require_once "siteConfig.php";

	authenticateUser($account,$password);
	drawTopNavigation();

/*
if (validateInput($_REQUEST["projectID"])) {
	$userAccessMode = $loggedInUser->accessModeForProject($_REQUEST["projectID"]);
}
*/
$accessString = "edit";

if ($_REQUEST["action"] != "doProjectRemove" && $_REQUEST["action"] != "processSpec" && $_REQUEST["action"] != "newProject")  {
	print "<table cellpadding='0' cellspacing='0' border='0' width='100%'>
	<tr>
		<td>";
		
	writeProjectInfoV2($_REQUEST["projectID"]);
		
	print "</td>
	</tr>
	<tr><td height='1' bgcolor='#cccccc'><img src='" . phImage("spacer.gif") . "' width='10' height='1'></td></tr>
	<tr><td height='5'><img src='" . phImage("spacer.gif") . "' width='10' height='5'></td></tr>
	<tr>
		<td>";
		
}

$projectTabs = new AquaTabs("projectTabs");
$projectTabs->setTabTitles(array("Summary","Contacts","Deliverables","Assets"));
$projectTabs->setRemovedKeys(array("buildID","specID"));
$projectTabs->setDefaultViewFor(1,'$projectTabs->setDisabledTabs(NULL); $projectTabs->display("top"); writeProjectContactsV2(' . $_REQUEST["projectID"] . ',"edit");');
$projectTabs->setDefaultViewFor(2,'$projectTabs->setDisabledTabs(NULL); $projectTabs->display("top"); displayDeliverableSelector(' . $_REQUEST["projectID"] . '); listDeliverables($myProject,$accessString);');

if ($_REQUEST["action"] == "newProject") {
	$projectTabs->setSelectedTab(-1);
	$projectTabs->setDisabledTabs(array(0,1,2,3,4,5));
	$projectTabs->display("top");

	$myProject = new Project();
	
	printNewProjectForm($myProject,"doNew",NULL);
}
else if ($_REQUEST["action"] == "doNew") {
	$projectTabs->setSelectedTab(-1);
	$projectTabs->setDisabledTabs(array(0,1,2,3,4,5));
	$myProject = new Project();

	$myProject->setValueForKey($_REQUEST["projectName"],"name");
	$myProject->setValueForKey($_REQUEST["description"],"description");
	$myProject->setValueForKey(1,"statusID");
	$myProject->setValueForKey($_SESSION["currentUserID"],"modifierID");
	$myProject->setValueForKey("NOW()","creationDate");
	
	if (!$_REQUEST["projectName"]) { $projectTabs->display("top"); printNewProjectForm($myProject,"doNew","Please supply a Project Name."); }
	else if (!$_REQUEST["description"]) { $projectTabs->display("top"); printNewProjectForm($myProject,"doNew","Please supply a description."); }
	else {
		$myProject->saveToDB();
		
		if ($myProject->valid) {
			/*
			// Add Creator as a contact
			$myContact = new Contact();
			$myContact->initWithUserID($currentUserID);
			
			if ($myContact->valid) {
				if ($myProject->addContact($myContact)) {
					$myContact->setAccessModeforProject(1,$myProject->id);
					redirectToPage("projectView?projectID=$myProject->id");
				}
				else {
					print "There was an error adding your contact info to the newly created project. <a href='projectView?projectID=$myProject->id'>Go here to your project</a>.";
				}
			}
			else { print "There was an error adding your contact info to the newly created project. <a href='projectView?projectID=$myProject->id'>Go here to your project</a>."; }
			*/
			
			redirectToPage("projectView?projectID=$myProject->id");
		}
		else { print "There was an error saving your project."; }
	}
}
else if ($_REQUEST["projectID"] && $specID && $moduleID && $_REQUEST["action"] == "removeModule") {
	if (validateInput($moduleID)) {
		$moduleInstance = new ModuleInstance($moduleID);
		$moduleInstance->removeFromDB();
	}
	redirectToPage("projectView.php?" . $_SERVER['QUERY_STRING']);
}
else if ($_REQUEST["projectID"] && $specID && !$_REQUEST["action"]) {
	if (validateInput(array($_REQUEST["projectID"],$specID))) {
		$mySpec = new Specification($specID);
		
		if ($mySpec->valid) {
			if ($mySpec->statusID == (3 | NULL)) {
				$projectTabs->setAltTabTitles(array($mySpec->name()));
				$projectTabs->altTabPosition = "right";
				$projectTabs->setSelectedAltTab(0);
				$projectTabs->setSelectedTab(-1);
				$projectTabs->display("top");
				
				if ($userAccessMode == 1) {
					drawSpecificationTabs($_REQUEST["projectID"],$specID,$_REQUEST["specTabs_activeTab"]);
				}
				printSpecDetail($specID,$accessString,"summary");
			}
			else if ($mySpec->statusID == 1) {
				$projectTabs->setAltTabTitles(array($mySpec->name()));
				$projectTabs->altTabPosition = "right";
				$projectTabs->setSelectedAltTab(0);
				$projectTabs->setSelectedTab(-1);
				$projectTabs->display("top");
				
				print "<div class='greyBox'><table cellpadding='2' cellspacing='2' border='0' width='100%'>
					<tr>
						<td><img src='$imageLoc/lock32.gif'></td>
						<td><span class='defaultErrorText'>WARNING</span><br><span class='defaultText'>This specification has been locked and submitted for processing to the CSS Team.<BR>To make further changes, processing must be suspended, which may impact this item's schedule within the approval/work queue.</span></td>
						";
				if ($userAccessMode == 1) {
					print "<td><a onclick='if(confirm(\"Are you sure you wish to suspend this specification? This may effect your delivery schedule.\")) { self.location.href = \"projectView.php?projectID=" . $_REQUEST["projectID"] . "&specID=$specID&action=doSuspendSpec\"; }'><img src='$imageLoc/suspendProcessing.gif'></td>";
				}
				print "</tr>
					</table></div>";
				
				print "<div class='whiteBox'>";
/*
				writeProjectInfoV2($_REQUEST["projectID"]);
							
				print "<hr>";
				
				writeProjectInfoLong($_REQUEST["projectID"]);
				writeProcessContacts($_REQUEST["projectID"]);
				writeSpecificationInfoLong($specID);
*/
				if ($mySpec->customizationCount() > 0) { printSpecDetail($specID,"view","full"); }
				else { print "<span class='defaultErrorText'>WARNING: This spec has no customizations associated with it. The result with be a retail load of Mac OS X.</span><br><br>"; }
				
				writeCPUInfo($specID);
				
				print "</div></html>";
			}
			else {
			
			}
		}
	}
}
else if ($_REQUEST["projectID"] && $specID && $_REQUEST["action"] == "processSpec") {
	// Show the processing screen
	if (validateInput(array($_REQUEST["projectID"],$specID))) {
		$mySpec = new Specification($specID);
		
		if ($mySpec->valid) {

			print "<div class='greyBox' style='text-align:left'><span class='defaultTextBold'>CAUTION:</span> <span class='defaultText'>You are about to submit this build and all associated customizations for review by the CSS Team. Be aware that this build will become locked from editing as it is evaluated. If you later wish to make further changes, you will need to unlock the build, suspending any processing which has already been completed by the team. Note that resubmitting a build may negatively impact the build's schedule within the approval/work queue.
			<br><br>
			<span class='defaultTextBold'>What will happen:</span> Clicking the 'Process' button will begin the CSS approval process. A summary email including all the data shown below will be sent to the contacts listed. A CSS engineer will be assigned to this build, and will review the customizations specified and determine if further clarification is required. The engineer will then contact you with any remaining questions, or with details on an expected time frame for the build to be completed.
			<br><br>
			<center>
			<a href='projectView.php?projectID=" . $_REQUEST["projectID"] . "&projectTabs_activeTab=2'><img src='$imageLoc/cancel.gif' border='0'></a> <a href='projectView.php?projectID=" . $_REQUEST["projectID"] . "&specID=$specID&action=doProcessSpec'><img src='$imageLoc/processPulse.gif' border='0'></a>
			</center>
			</span></div><div class='whiteBox' style='text-align:left'>";

			writeProjectInfoPlain($_REQUEST["projectID"]);

			print "<hr>";

			writeProjectInfoLong($_REQUEST["projectID"]);
			print "<br>";
			writeProcessContacts($_REQUEST["projectID"]);
			writeSpecificationInfoLong($specID);

			if ($mySpec->customizationCount() > 0) {
				printSpecDetail($specID, "email", "full", 0);
			}
			else {
				print "<span class='defaultErrorText'>WARNING: This spec has no customizations associated with it. The result with be a retail load of Mac OS X.</span><br><br>";
			}

			writeCPUInfo($specID);
			print "</div>";
		}
	}
}
else if ($_REQUEST["projectID"] && $specID && $_REQUEST["action"] == "doProcessSpec") {
	// Email the information and show the processing completion screen.
	
	if (validateInput(array($_REQUEST["projectID"],$specID))) {
		$myProject = new Project($_REQUEST["projectID"]);
		$mySpec = new Specification($specID);
		
		if ($myProject->valid && $mySpec->valid) {
			// Chage Spec Status
			$mySpec->statusID = 1;
			$mySpec->saveToDB();
		
			// Grab the page with the email output on it
			$handle = fopen($hostRoot . $appRoot . "/processEmail.php?projectID=" . $_REQUEST["projectID"] . "&specID=$specID&passthrucode=1c0d32rul37h3m4ll", "r");
			while (!feof($handle)) { $buffer .= fgets($handle, 4096); }
			fclose($handle);
		
			// Headers
			$headers = "From: CSS Team <css@apple.com>\r\n";
			$headersÊ.= "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: multipart/alternative; boundary=cssPART\r";
		
			if (mail($myProject->contactsAsList(TRUE) . ",CSS Team <css@apple.com>",
				"CSS Build Summary for $myProject->name : $mySpec->name",$buffer,$headers)) {
				redirectToPage("projectView.php?projectID=" . $_REQUEST["projectID"]);
			}
		}
		else { /* There was an error */ }
	}
	else { /* There was an error */ }
}
else if ($_REQUEST["projectID"] && $specID && $_REQUEST["action"] == "doSuspendSpec") {
	// Email the suspension notice and suspend the specification
	
	if (validateInput(array($_REQUEST["projectID"],$specID))) {
		$myProject = new Project($_REQUEST["projectID"]);
		$mySpec = new Specification($specID);
		
		if ($myProject->valid && $mySpec->valid) {
			// Chage Spec Status
			$mySpec->statusID = 3;
			$mySpec->saveToDB();
		
			// Grab the page with the email output on it
			$handle = fopen($hostRoot . $appRoot . "/suspendEmail.php?projectID=" . $_REQUEST["projectID"] . "&specID=$specID&passthrucode=1c0d32rul37h3m4ll", "r");
			while (!feof($handle)) { $buffer .= fgets($handle, 4096); }
			fclose($handle);
		
			// Headers
			$headers = "From: CSS Team <css@apple.com>\r\n";
			$headersÊ.= "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: multipart/alternative; boundary=cssPART\r";
		
			if (mail($myProject->contactsAsList(TRUE) . ",CSS Team <css@apple.com>",
				"CSS Build Processing Suspended for $myProject->name : $mySpec->name",$buffer,$headers)) {
				redirectToPage("projectView.php?projectID=" . $_REQUEST["projectID"] . "&specID=$specID");
			}
		}
		else { /* There was an error */ }
	}
	else { /* There was an error */ }
}
else if (!$_REQUEST["projectTabs_activeTab"] || $_REQUEST["projectTabs_activeTab"] == 0) {
	// Summary Tab
	$projectTabs->setSelectedTab(0);

	if ($_REQUEST["action"] == "doProjectRemove") {
		if (validateInput($_REQUEST["projectID"])) {
			$myProject = new Project($_REQUEST["projectID"]);
			
			if ($myProject->valid) {
				$myProject->removeFromDB();
				redirectToPage("main.php");
			}
		}
	}
	else if ($_REQUEST["action"] == "edit") {
		if (validateInput($_REQUEST["projectID"])) {
			$projectTabs->setDisabledTabs(array(1,2,3,4,5));
			$projectTabs->display("top");
		
			$myProject = new Project($_REQUEST["projectID"]);
			
			if ($myProject->valid) { printNewProjectForm($myProject,"doEdit",NULL); }
		}
		else { print "The provided project ID was either not a number or less then 0. Questions questions, too many questions. You want a shard? Here!"; }
	}
	else if ($_REQUEST["action"] == "doEdit") {
		if (validateInput($_REQUEST["projectID"])) {
			$projectTabs->setDisabledTabs(array(1,2,3,4,5));
			$myProject = new Project($_REQUEST["projectID"]);
			
			if ($myProject->valid) {
				if (!$projectName) { printNewProjectForm($myProject,"edit","Please supply a Project Name."); }
				else if (!$description) { printNewProjectForm($myProject,"edit","Please supply a description."); }
				else {
					$myProject->name = $projectName;
					$myProject->description = $description;
					$myProject->saveToDB();
					
					if ($myProject->valid) {
						$projectTabs->setDisabledTabs();
						$projectTabs->display("top");
						
						writeProjectSummary($_REQUEST["projectID"]);
					}
					else {
						// There was an error saving to DB
					}
				}
			}
			else {
				// This project wasn't valid
			}
		}
		else {
			// This project ID wasn't valid
		}
	}
	else {
		if (!$_REQUEST["projectID"]) {
			// Uhm. Nothing to see here.
			$projectTabs->setDisabledTabs(array(0,1,2,3,4));
			$projectTabs->display("top");
	
			print "There was no projectID provided.";
		}
		else {
			$projectTabs->display("top");
			writeProjectSummary($_REQUEST["projectID"]);
		}
	}
}
else if ($_REQUEST["projectTabs_activeTab"] == 1) {
	// Contact Tab
	$projectTabs->setSelectedTab(1);

	if ($_REQUEST["action"] == "doAdd") {
		$formDataArray = array(
			name => $_REQUEST["name"],
			email => $_REQUEST["email"],
			phone => $_REQUEST["phone"],
			roleID => $_REQUEST["roleID"]
		);

		$projectTabs->setSelectedTab(1);
		$projectTabs->setDisabledTabs(array(0,2,3));
		
		if (is_null($formDataArray["name"])) {
			$projectTabs->display("top");
			printNewContactForm($_REQUEST["projectID"],$formDataArray,"doAdd","Please supply a name for this contact.");
		}
		else {
			// Do contact add
		
			if (validateInput($_REQUEST["projectID"])) {
				$myProject = new Project($_REQUEST["projectID"]);
				
				if ($myProject->valid) {
					$contact = new Contact;
					
					$contact->setValueForKey($formDataArray["name"],"name");
					$contact->setValueForKey($formDataArray["email"],"email");
					$contact->setValueForKey($formDataArray["phone"],"phone");
					$contact->setValueForKey($formDataArray["roleID"],"roleID");

					$contact->saveToDB();
					
					if ($contact->valid) {
						if ($myProject->joinWithObjectInTable($contact,"projects_contacts_join")) {
							eval($projectTabs->defaultViewFor(1)); // Display contact tab
						}
						else { print "There was a problem saving your contact to the database."; }
					}
					else { print "There was a problem saving your contact to the database."; }
				}
			}
			else { print "The provided project ID was either not a number or less then 0. Questions questions, too many questions. You want a shard? Here!"; }
		}
	}
	else if ($_REQUEST["action"] == "doEdit") {
		$formDataArray = array(
			contactID => $_REQUEST["contactID"],
			name => $_REQUEST["name"],
			email => $_REQUEST["email"],
			phone => $_REQUEST["phone"],
			roleID => $_REQUEST["roleID"]
		);
		
		$projectTabs->setSelectedTab(1);
		$projectTabs->setDisabledTabs(array(0,2,3));
		
		if (!$formDataArray["name"]) {
			$projectTabs->display("top");
			printNewContactForm($_REQUEST["projectID"],$formDataArray,"doEdit","Please supply a name for this contact.");
		}
		else {
			// Do contact editing
			
			if (validateInput($_REQUEST["contactID"])) {
				$contact = new Contact($_REQUEST["contactID"]);
				
				if ($contact->valid) {			
					$contact->setValueForKey($formDataArray["name"],"name");
					$contact->setValueForKey($formDataArray["email"],"email");
					$contact->setValueForKey($formDataArray["phone"],"phone");
					$contact->setValueForKey($formDataArray["roleID"],"roleID");
					
					$contact->saveToDB();
				
					if ($contact->valid) { eval($projectTabs->defaultViewFor(1)); } // Display contact tab
					else { print "There was a problem saving your contact to the database."; }
				}
				else { print "There was a problem getting your contact to the database."; }
			}
			else { print "The provided contact ID was either not a number or less then 0. Questions questions, too many questions. You want a shard? Here!"; }
		}
	}
	else if ($_REQUEST["action"] == "doRemove") {
		if (validateInput(array($_REQUEST["contactID"],$_REQUEST["projectID"]))) {
			$contact = new Contact($_REQUEST["contactID"]);
			$project = new Project($_REQUEST["projectID"]);
			
			// Remove Contact
			
			if ($project->valid) {			
				if ($contact->valid) {
					$splitStatus = $project->splitFromObjectInTable($contact,"projects_contacts_join");
					$contact->removeFromDB();
					
					if (!$contact->valid && $splitStatus) { eval($projectTabs->defaultViewFor(1)); } // Display contact tab
				}
				else { print "The provided contact ID was not found in the database."; }
			}
			else { print "The provided project ID was not found in the database."; }
		}
		else { print "The provided contact ID was either not a number or less then 0. Questions questions, too many questions. You want a shard? Here!"; }
	}
	else if ($_REQUEST["action"] == "edit") {
		if (validateInput($_REQUEST["contactID"])) {
			$contact = new Contact($_REQUEST["contactID"]);
			
			// Show contact edit
			
			if ($contact->valid) {
				$formDataArray = array(
					contactID => $contact->id,
					fullName => $contact->name,
					email => $contact->email,
					phone => $contact->phone,
					roleID => $contact->roleID
				);
				
				$projectTabs->setSelectedTab(1);
				$projectTabs->setDisabledTabs(array(0,2,3));
				$projectTabs->display("top");
				printNewContactForm($_REQUEST["projectID"],$formDataArray,"doEdit",NULL);
			}
			else { print "The provided contact ID was not found in the database."; }
		}
		else { print "The provided contact ID was either not a number or less then 0. Questions questions, too many questions. You want a shard? Here!"; }
	}
	else if ($_REQUEST["action"] == "add") {
		// Show contact add form
		$projectTabs->setSelectedTab(1);
		$projectTabs->setDisabledTabs(array(0,2,3));
		$projectTabs->display("top");
		
		printNewContactForm($_REQUEST["projectID"],NULL,"doAdd",NULL);
	}
	else {
		// Show contacts
		eval($projectTabs->defaultViewFor(1));
	}
}
else if ($_REQUEST["projectTabs_activeTab"] == 2) {
	// Deliverables Tab
	$projectTabs->setSelectedTab(2);
	$myProject = new Project($_REQUEST["projectID"]);
	
	if ($myProject->valid) {
	
		if ($_REQUEST["action"] == "addDeliverable") {
			// Add a Deliverable to the Project
			
			$projectTabs->setSelectedTab(2);
			$projectTabs->setDisabledTabs(array(0,1,3));
			$projectTabs->display("top");
			
			displayNewDeliverableForm($myProject,$_REQUEST["moduleID"]);
		}
		else if ($_REQUEST["action"] == "doAddDeliverable") {
			$deliverable = new Deliverable;
			
			$deliverable->setValueForKey($_REQUEST["moduleID"],"parentModuleID");
			$deliverable->setValueForKey(parseModuleInput($_REQUEST),"data");
			$deliverable->setValueForKey($_REQUEST["note"],"note");

			$deliverable->saveToDB();
			
			if ($deliverable->valid) {
				$joinStatus = $myProject->joinWithObjectInTable($deliverable,"projects_deliverables_join");
			
				if ($joinStatus) { eval($projectTabs->defaultViewFor(2)); }
				else {
					$projectTabs->setDisabledTabs(array(0,1,2,3,4));
					$projectTabs->display("top");
					print "There was an error joining your deliverable with the project.";	
				}
			}
			else {
				$projectTabs->setDisabledTabs(array(0,1,2,3,4));
				$projectTabs->display("top");
				print "There was an error saving your deliverable to the database.";	
			}
		}
		else if ($_REQUEST["action"] == "editSpec") {
			if (validateInput($specID)) {
				$mySpec = New Specification($specID);
				
				if ($mySpec->valid) {
					$projectTabs->setDisabledTabs(array(0,1,3));
					
					$formDataArray = array(
						"name" => $mySpec->name,
						"description" => $mySpec->description,
						"specID" => $mySpec->id
					);
					
					$projectTabs->display("top");
					printNewSpecForm($myProject,$formDataArray,null,"doEditSpec");
				}
				else { /* This spec was not valid. */ }
			}
			else { /* This id was not a valid mysql resource. */ }
		}
		else if ($_REQUEST["action"] == "doEditSpec") {
			$formDataArray = array(
				"name" => $name,
				"description" => $description,
				"specID" => $specID
			);
			
			$projectTabs->setDisabledTabs(array(0,1,3));
			
			if ($formDataArray[name] == "null") { $projectTabs->display("top"); printNewSpecForm($myProject,$formDataArray,"Please supply a specification name.","doEditSpec"); }
			else {
				// Do Edit Spec
				
				if ($myProject->valid && validateInput($specID)) {
					$mySpec = New Specification($specID);
					
					if ($mySpec->valid) {
						// Save new data to CPU
						$mySpec->name = $formDataArray[name];
						$mySpec->description = $formDataArray[description];
						$mySpec->saveToDB();
						
						if ($mySpec->valid) { eval($projectTabs->defaultViewFor(2)); }
						else { $projectTabs->display("top"); printNewSpecForm($myProject,$formDataArray,"Error. The spec could not be saved.","doEditSpec"); }
					}
					else { $projectTabs->display("top"); printNewSpecForm($myProject,$formDataArray,"Error. The spec is not valid.","doEditSpec"); }
				}
				else { $projectTabs->display("top"); printNewSpecForm($myProject,$formDataArray,"Error. The project is not valid.","doEditSpec"); }
			}
		}
		else if ($_REQUEST["action"] == "deleteSpec") {
			if (validateInput($specID)) {
				$mySpec = New Specification($specID);
				
				if ($mySpec->valid) {
					// Delete Spec and all connections to builds.
					if ($mySpec->unassociateCPUs()) {
						// All builds were successfully unassociated. Set status of spec to 'userRemoved'.
						if ($mySpec->updateStatus(5)) {
							// Yep it all worked out ok.
							eval($projectTabs->defaultViewFor(2));
						}
						else { /* Crap! */ }
					}
					else { /* Crap! */ }
				}
				else { /* Crap! */ }
			}
			else { /* Crap! */ }
		}
		else if ($_REQUEST["action"] == "addCPU") {
			// Add CPU/Build
			$projectTabs->setDisabledTabs(array(0,1,3));
			$projectTabs->display("top");
			
			printNewCPUForm($myProject);
		}
		else if ($_REQUEST["action"] == "doAddCPU") {
			$formDataArray = array(
				"productFamily" => $productFamily,
				"basePart" => $basePart,
				"specID" => $specID
			);
			
			$projectTabs->setDisabledTabs(array(0,1,3));
			
			if ($formDataArray[productFamily] == "null") { $projectTabs->display("top"); printNewCPUForm($myProject,$formDataArray,"Please select a product family from the popup"); }
			else {
				// Do Add CPU
				
				if ($myProject->valid) {
					$newCPU = New Build();
					$newCPU->productFamily = $formDataArray[productFamily];
					$newCPU->marketingPart = $formDataArray[basePart];
					$newCPU->saveToDB();
					
					if ($newCPU->valid) {
						if ($myProject->addBuild($newCPU)) {
							// Add was successful.
							
							if ($specID && validateInput($specID)) {
								$mySpec = New Specification($specID);
								
								if ($mySpec->valid) {
									if ($mySpec->addBuild($newCPU)) { eval($projectTabs->defaultViewFor(2)); }
									else { printNewCPUForm($myProject,$formDataArray,"Error. There was an error adding the CPU to the specification."); }
								}
								else { printNewCPUForm($myProject,$formDataArray,"Error. There specification selected was not valid."); }
							}
							else {
								$projectTabs->setDisabledTabs(NULL); eval($projectTabs->defaultViewFor(2)); }
						}
						else { printNewCPUForm($myProject,$formDataArray,"Error. There was an error adding the CPU to the project."); }
					}
					else { printNewCPUForm($myProject,$formDataArray,"Error. The created CPU could not be saved."); }
				}
				else { printNewCPUForm($myProject,$formDataArray,"Error. The project is not valid."); }
			}
		}
		else if ($_REQUEST["action"] == "editCPU") {
			if (validateInput($buildID)) {
				$myCPU = New Build($buildID);
				
				if ($myCPU->valid) {
					$projectTabs->setDisabledTabs(array(0,1,3));
					
					$formDataArray = array(
						"productFamily" => $myCPU->productFamily,
						"basePart" => $myCPU->marketingPart,
						"specID" => $myCPU->specID(),
						"buildID" => $myCPU->id
					);
					
					$projectTabs->display("top");
					printNewCPUForm($myProject,$formDataArray,null,"doEditCPU");
				}
				else { /* This CPU was not valid. */ }
			}
			else { /* This id was not a valid mysql resource. */ }
		}
		else if ($_REQUEST["action"] == "doEditCPU") {
			$formDataArray = array(
				"productFamily" => $productFamily,
				"basePart" => $basePart,
				"specID" => $specID,
				"buildID" => $buildID
			);
			
			$projectTabs->setDisabledTabs(array(0,1,3));
			
			if ($formDataArray[productFamily] == "null") { $projectTabs->display("top"); printNewCPUForm($myProject,$formDataArray,"Please select a product family from the popup","doEditCPU"); }
			else {
				// Do Edit CPU
				
				if ($myProject->valid && validateInput($buildID)) {
					$myCPU = New Build($buildID);
					
					if ($myCPU->valid) {
						// Save new data to CPU
						$myCPU->productFamily = $formDataArray[productFamily];
						$myCPU->marketingPart = $formDataArray[basePart];
						$myCPU->saveToDB();
						
						if ($myCPU->valid) {
							// Should we disconnect the build from a spec?
							$cpuSpecID = $myCPU->specID();
													
							if ($specID == "null" && $cpuSpecID == null) { eval($projectTabs->defaultViewFor(2)); }
							else if ($specID == "null" && $cpuSpecID != null) {
								// DIsconnect Spec
								$mySpec = New Specification($cpuSpecID);
								
								if ($mySpec->valid) {
									// Remove CPU from spec
									if ($mySpec->removeBuild($myCPU)) { eval($projectTabs->defaultViewFor(2)); }
									else { $projectTabs->display("top"); printNewCPUForm($myProject,$formDataArray,"The build could not be removed from the specification","doEditCPU"); }
								}
								else { $projectTabs->display("top"); printNewCPUForm($myProject,$formDataArray,"That specification was not a valid choice","doEditCPU"); }
							}
							else if ($specID != "null" && $cpuSpecID != null && $specID == $cpuSpecID) { eval($projectTabs->defaultViewFor(2)); }
							else if ($specID != "null" && $cpuSpecID != null && $specID != $cpuSpecID) {
								// Disconnect Spec and ReConnect
								$mySpec = New Specification($cpuSpecID);
								
								if ($mySpec->valid) {
									// Remove CPU from spec
									if ($mySpec->removeBuild($myCPU)) {
										$mySpec = New Specification($specID);
										
										// Add CPU to spec
										if ($mySpec->addBuild($myCPU)) { eval($projectTabs->defaultViewFor(2)); }
										else { $projectTabs->display("top"); printNewCPUForm($myProject,$formDataArray,"The build could not be added to another specification","doEditCPU"); }
									}
									else { $projectTabs->display("top"); printNewCPUForm($myProject,$formDataArray,"The build could not be moved to another specification","doEditCPU"); }
								}
							}
							else if ($specID != "null" && $cpuSpecID == null) {
								// Add CPU to spec
								$mySpec = New Specification($specID);
										
								if ($mySpec->addBuild($myCPU)) { eval($projectTabs->defaultViewFor(2)); }
								else { $projectTabs->display("top"); printNewCPUForm($myProject,$formDataArray,"The build could not be added to the specification","doEditCPU"); }
							}
							else { eval($projectTabs->defaultViewFor(2)); }
						}
						else { printNewCPUForm($myProject,$formDataArray,"Error. The CPU could not be saved.","doEditCPU"); }
					}
					else { printNewCPUForm($myProject,$formDataArray,"Error. The CPU is not valid.","doEditCPU"); }
				}
				else { printNewCPUForm($myProject,$formDataArray,"Error. The project is not valid.","doEditCPU"); }
			}
		}
		else if ($_REQUEST["action"] == "deleteCPU") {
			if (validateInput($buildID)) {
				$myCPU = New Build($buildID);
				
				if ($myCPU->valid) {
					// Delete build and all connections to project and spec.
					
					if ($myProject->removeBuild($myCPU)) {
						// Build disconnected from project.
						$myCPU->removeFromDB();
						
						if ($myCPU->valid) { /* The build was not removed correctly */ }
						else { eval($projectTabs->defaultViewFor(2)); }
					}
					else { /* There was an error removing the build from the project. */ }
				}
				else { /* This buildID was not valid. */ }
			}
		}
		else if ($_REQUEST["action"] == "removeCPU") {
			if (validateInput($buildID)) {
				$myCPU = New Build($buildID);
				
				if ($myCPU->valid) {
					if ($myCPU->removeFromSpec()) { eval($projectTabs->defaultViewFor(2)); }
					else { /* There was a problem removing the build from the join table. */ }
				}
				else { /* This buildID was not valid. */ }
			}
		}
		else {
			// Show standard deliverables tables
			eval($projectTabs->defaultViewFor(2));
		}
	}
	else {
		$projectTabs->setDisabledTabs(array(0,1,2,3,4));
		$projectTabs->display("top");
		print "There was an error accessing that project ID.";	
	}
}
else if ($_REQUEST["projectTabs_activeTab"] == 3) {
	// Files Tab
	
	$projectTabs->setSelectedTab(3);
	$projectTabs->display("top");
	writeProjectFiles($_REQUEST["projectID"], $userAccessMode);
	if ($userAccessMode == 1) {
		writeUploadFileTable($_REQUEST["projectID"]);
	}
}
else {
	// Uhm. Nothing to see here.
	$projectTabs->setDisabledTabs(array(0,1,2,3,4));
	$projectTabs->display("top");
	
	print "There provided tab ID was not valid";
}

$projectTabs->display("bottom");

print "</td></tr></table>";

drawBottomNavigation();

?>
