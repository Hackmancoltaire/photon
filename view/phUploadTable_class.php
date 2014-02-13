<?php

class phUploadTable {
		
	var $iconSize;
	var $deniedExtensions;
	var $allowedExtensions;
	var $uploadCallback;

	function phUploadTable($id=null) {
		if (!(is_null($id))) {
			$this->id = uniqid("phUploadView-");
		}
		
		$this->iconSize = 32;
		$this->deniedExtensions = array();
		$this->uploadCallback = null;
	}
	
	function setCallback($callback=null) {
		if (!(is_null($callback))) { $this->$callback = $callback; }
	}
	
	function allowExtension($ext=null) {
		if (!(is_null($ext))) {
			if (is_array($ext)) {
				$this->allowedExtensions = array_merge($this->allowedExtensions, $ext);
			}
			else {
				$this->allowedExtensions[] = $ext;
			}
		}	
	}
	
	function denyExtension($ext=null) {
		if (!(is_null($ext))) {
			if (is_array($ext)) {
				$this->deniedExtensions = array_merge($this->deniedExtensions, $ext);
			}
			else {
				$this->deniedExtensions[] = $ext;
			}
		}
	}
	
	function setIconSize($size=null) {
		if (!(is_null($size))) {
			$this->iconSize = $size;
		}
	}
	
	function setUploadCallback($callback=null) {
		// Valid callback are "url" or "URL" or "script" or "SCRIPT"
		if (!(is_null($callback))) {
			$this->uploadCallback = $callback;
		}
	}
	
	function display() {
	
		print "<script type='text/javascript'>" 
			. ((count($this->deniedExtensions) > 0) ? "\n\t\t\tvar deniedExtensions = [ '" . implode("','", $this->deniedExtensions) . "' ];":"") . "\n"
			. ((count($this->allowedExtensions) > 0) ? "\n\t\t\tvar allowedExtensions = [ '" . implode("','", $this->allowedExtensions) . "' ];":"") . "
			var phUploaderIconSize = " . $this->iconSize . ";
			
			var phUploaderCallback = " . ((strpos($this->uploadCallback,"function") == 0 && strpos($this->uploadCallback,"function") !== FALSE) ? $this->uploadCallback:(($this->uploadCallback != null) ? ("'" . $this->uploadCallback . "';"):"null")) ."
			
		</script>
		
		<div style='border: 1px solid #cccccc;'>
					<form name='phUploadDropZone' action='#' class='phUploadDropZone'>

			<div id='phUploadFiles' style='width: 100%; min-height: 70px; max-height: 200px; position: relative; color: #cccccc; overflow: auto;' ondragenter='drag_Enter(event);'>
				<div id='interact' style='position: absolute; width: 100%; height: 100%; background-color: rgba(0,0,0,.8); display: none; z-index: 10;' ondragleave='drag_Leave(event);'>
						<input name='dropFile' type='file' class='file hidden' noscript='true' onChange='drag_Drop(event)'>
						<div class='fakefile' style='color: white; position: absolute; top: 10px; bottom: 10px; right: 10px; left: 10px; border: 4px dashed white; text-align: center; vertical-align: middle; line-height: 90%;'>
							<div style='position: absolute; top: 47%; left: 0; right: 0; color: inherit'>Drag a file here to upload</div>
						</div>
						
				</div>
			</div>
			<div id='upload_controlsBar'>
				<div id='phUploader_add'><input name='addFile' type='file' class='file hidden' noscript='true' size='1' style='position: absolute; left: -20px;' onChange='drag_Drop(event)'></div>
				<div id='phUploader_submit' onClick='upload(true)'></div>
			</div>
		</div>
		</form>
		<iframe id='phUploadFrame' name='phUploadFrame' scrolling='No' style='display: none'>#</iframe>";
	}

}

?>