<?php

class File extends MySQL4Adaptor {

	var $objectDBConnections = array(
		"id"			=> array("fileID",1),
		"name"			=> array("name",2),
		"type"			=> array("type",2),
		"location"		=> array("location",2),
		"note"			=> array("note",2)
	);

	function File($id=null) {
		$this->alloc();
		$this->connectionName = "Mercury";
		$this->database = "Mercury";
		$this->tableName = "file";

		if ($id) { $this->initWithID($id); }
	}
	
	function initWithFileHandler($UUID=null,$location=null,$filename=null,&$error) {
		// The file upload handler creates a file object and passes in the folder location AND the UUID
		// of the folder for the file. For example the file on the file system will be in a location like
		// "/tmp/PHOTON-uploads/1D528A60-EB95-0001-1DDB-1E6DF9DE1D49/myFile.pdf" and will provide this
		// information to the object as:
		//
		// uuid = "1D528A60-EB95-0001-1DDB-1E6DF9DE1D49"
		// location = "/tmp/PHOTON-uploads/1D528A60-EB95-0001-1DDB-1E6DF9DE1D49/myFile.pdf"
		// filename = "myFile.pdf"
		//
		// This should give you ample information to process the file and initialize your object. It is
		// recommended that you move the file into a more permanent location as the "/tmp" directory
		// is cleaned occaisionally depending on your systems configuration. It might also be a good
		// practive to remove the storage folder with the UUID to keep the uploads folder uncluttered.
	
		$storageLocation = "/Users/Shared/Files/";
	
		if (!(is_null($UUID)) && !(is_null($location)) && !(is_null($filename))) {

			$this->name = $filname;
			
			if (rename($location,$storageLocation . $filename)) {
				// File moved. Continue.
				
				// Remove the temporary storage location (optional)
				$path = pathInfo($location);
				rmdir($path["dirname"]);
				
				$this->type = mime_content_type($storageLocation . $fileName);
				$this->location = $storageLocation . $filename;
				
				$this->saveToDB();
				
				if ($this->valid) { return true; }
				else { return false; }
			}
			else {
				// File could not move. FREAK OUT!
				return false;			
			}
		}
		else { return false; }
	}
}

?>