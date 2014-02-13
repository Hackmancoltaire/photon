/**************************************************************************************************
	Predefined Variables
***************************************************************************************************/				

	var phUploads = new Array();
	var phUploadCount = 0;
	var phUploadDrop = false;

	var http_requests = new Array();

	if (window.XMLHttpRequest) { var http_request = new XMLHttpRequest(); }
	else { var http_request=false; }
	
/**************************************************************************************************
	AJAX Server Requests
***************************************************************************************************/				

	function build_HttpRequest() {
		if (window.XMLHttpRequest) { // Mozilla, Safari,...
			var request = new XMLHttpRequest();
			
			if (request.overrideMimeType) { request.overrideMimeType('text/xml'); }
		}
		else if (window.ActiveXObject) { // IE
			try { var request = new ActiveXObject("Msxml2.XMLHTTP"); }
			catch (e) {
				try { var request = new ActiveXObject("Microsoft.XMLHTTP"); } catch (e) { }
			}
		}

		if (!request) {
			alert('Cannot create an XMLHTTP instance');
			return false;
		}

		return request;
	}

//** FUNCTION: Make Request to Server passing GET variables

	function makeRequest(url,do_function) {
		http_request = build_HttpRequest();
		var time = new Date();				
		if (url.indexOf('?')>0) { url = url + '&time='+time.getTime(); }
		else { url = url + '?time='+time.getTime(); }

		http_request.onreadystatechange = do_function;
		http_request.open('GET', url, true);
		http_request.send(null);
	}	

/**************************************************************************************************
	Upload Form Functions
***************************************************************************************************/			
//** FUNCTION: Create Upload Form

	function drawViewForFileForm(form,state,context) {
		
		var fileArea = document.getElementById('phUploadFiles');
		var UUID = form.getAttribute("id").substring(form.getAttribute("id").lastIndexOf('- ') + 2, form.getAttribute("id").length);
		
		if (document.getElementById("phUploadView - " + UUID) != null) {
			// View exists. Update depending on state
			var newTable = document.getElementById("phUploadView - " + UUID);
			var tdZ = newTable.childNodes[0].childNodes;
						
			if (state == "wait") {
				// Do nothing, everything is fine. :-/
			}
			else if (state == "downloading") {
				if (tdZ[1].getAttribute("id") == "downloading") {
					// Download has already begun. Update the time and bar
					tdZ[1].childNodes[1].innerHTML = "<div style='width:" + context[3] + "%;'>&nbsp;<\/div>";
					tdZ[1].childNodes[2].innerHTML = context[4] + " of " + context[5] + " - " + context[2];
				}
				else {
					tdZ[1].setAttribute("id","downloading");
					
					var uploadBar = document.createElement("div");
					
					uploadBar.setAttribute("id","upload_bar");
					uploadBar.setAttribute("align","left");
					uploadBar.innerHTML = "<div style='width:" + context[3] + "%;'>&nbsp;<\/div>";
					
					tdZ[1].childNodes[1].innerHTML = context[4] + " of " + context[5] + " - " + context[2];
					tdZ[1].insertBefore(uploadBar, tdZ[1].childNodes[1]);
					
					tdZ[2].innerHTML = "";
				}
			}
			else if (state == "Success") {
				if (tdZ[1].getAttribute("id") == "downloading") {
					tdZ[1].childNodes[2].innerHTML = context[2];
					tdZ[1].removeChild(tdZ[1].childNodes[1]);
				}
				
				tdZ[2].innerHTML = "<img width='28' src='" + phAppServerRoot + "/PHOTON/images/Success.png" + "'>";
				tdZ[1].setAttribute("id","success");
				
				// Execute Callback
				if (isFunction(phUploaderCallback)) {
					phUploaderCallback(UUID);
				}
				else {
					makeRequest(phUploaderCallback + "&UUID=" + UUID,callbackReturn);
				}
			}
			else {
				// Any other states?
			}
		}
		else {
			// View does not exist. Create view
		
			var newTable = document.createElement("div");
			
			newTable.setAttribute("id","phUploadView - " + UUID);
			newTable.style.top = "0px";
			newTable.style.left = "0px";
			newTable.style.display = "none";
			newTable.style.zIndex = 3;
	
			var output = new Array();
	
			if ((phUploads.length % 2)==1) {
				newTable.style.backgroundColor = "rgb(234,244,255)";
			}
			
			var fileName = fileElement.value.substring(fileElement.value.lastIndexOf('/') + 1, fileElement.value.length).replace(/%20/g, ' ');
			var f = fileName.substring(fileName.lastIndexOf('.') + 1, fileName.length).toLowerCase();
			
			if (f == "bmp" || f == "gif" || f == "jpg" || f == "jpeg" || f == "tif" || f == "tiff" || f == "png" || f == "eps" || f == "pict") {
				var extImage = phAppServerRoot + "/PHOTON/images/icons/genericImage.png";
			}
			else if (f == "pdf") { var extImage = phAppServerRoot + "/PHOTON/images/icons/pdf.png"; }
			else if (f == "doc") { var extImage = phAppServerRoot + "/PHOTON/images/icons/word.png"; }
			else if (f == "dmg") { var extImage = phAppServerRoot + "/PHOTON/images/icons/dmg.png"; }
			else if (f == "fmp" || f == "fp5" || f == "fp6" || f == "fp7") { var extImage = phAppServerRoot + "/PHOTON/images/icons/fmp.png"; }
			else if (f == "html") { var extImage = phAppServerRoot + "/PHOTON/images/icons/html.png"; }
			else if (f == "key") { var extImage = phAppServerRoot + "/PHOTON/images/icons/keynote.png"; }
			else if (f == "mov" || f == "mpg" || f == "mpeg" || f == "3gpp" || f == "avi" || f == "dv" || f == "m4v" || f == "mp4") {
				var extImage = phAppServerRoot + "/PHOTON/images/icons/mov.png";
			}
			else if (f == "oo3") { var extImage = phAppServerRoot + "/PHOTON/images/icons/outliner.png"; }
			else if (f == "xls") { var extImage = phAppServerRoot + "/PHOTON/images/icons/excel.png"; }
			else if (f == "ppt") { var extImage = phAppServerRoot + "/PHOTON/images/icons/powerpoint.png"; }
			else if (f == "psd") { var extImage = phAppServerRoot + "/PHOTON/images/icons/psd.png"; }
			else if (f == "rtf" || f == "rtfd") { var extImage = phAppServerRoot + "/PHOTON/images/icons/rtf.png"; }
			else if (f == "ai") { var extImage = phAppServerRoot + "/PHOTON/images/icons/illustrator.png"; }
			else if (f == "indd") { var extImage = phAppServerRoot + "/PHOTON/images/icons/indesign.png"; }
			else if (f == "graffle") { var extImage = phAppServerRoot + "/PHOTON/images/icons/omnigraffle.png"; }
			else if (f == "pages") { var extImage = phAppServerRoot + "/PHOTON/images/icons/pages.png"; }
			else if (f == "txt" || f == "plist" || f == "php" || f == "css" || f == "js" || f == "xml") { var extImage = phAppServerRoot + "/PHOTON/images/icons/txt.png"; }
			else if (f == "vcf") { var extImage = phAppServerRoot + "/PHOTON/images/icons/vcf.png"; }
			else if (f == "zip" || f == "tar" || f == "gz" || f == "sit" || f == "sitx" || f == "rar" || f == "bzip") {
				var extImage = phAppServerRoot + "/PHOTON/images/icons/zip.png";
			}
			else { var extImage = phAppServerRoot + "/PHOTON/images/icons/generic.png"; }
			
			output.push("<div style='padding: 10px'>");
			output.push("<div style='display: table-cell; padding-right: 10px;'><img src='" + extImage + "' width='" + phUploaderIconSize + "'><\/div>");

			output.push("<div id='waiting' style='display: table-cell; vertical-align: top;	white-space: normal; width: 100%;'>");
			output.push("<div class='upload_filename'>" + fileName + "<\/div>");
			output.push("<div class='upload_stats'>Waiting for upload to Start<\/div>");
			output.push("<\/div>");

			output.push("<div style='display: table-cell; padding-left: 10px;'><img src='" + phAppServerRoot + "/PHOTON/images/closebox.png" + "' onClick='removeFile(\"" + UUID + "\")'><\/div>");
			output.push("<\/div>");

			newTable.innerHTML = output.join('');

			fileArea.appendChild(newTable);
		
			new Effect.Appear(newTable, { duration: .2 });
			new Effect.SlideDown(newTable, { duration: .3 });
		}
	}
	
	function removeFile(UUID) {		
		for (i=0; i < phUploadCount; i++) {
			if (phUploads[i] == UUID) {
				phUploads.splice(i,1);
				phUploadCount = phUploadCount - 1;
				
				formElement = document.getElementById("phUploadForm - " + UUID);
				formElement.parentNode.removeChild(formElement);
				deleteView = document.getElementById("phUploadView - " + UUID);
				
				new Effect.Fade(deleteView, { duration: .2 });
				new Effect.SlideUp(deleteView, { duration: .3, afterFinish: function() {
					deleteView.parentNode.removeChild(deleteView);					
				}});
			}
			
			if ((i % 2)==1) {
				element = document.getElementById("phUploadView - " + phUploads[i]);
				new Effect.Morph(element, { style:'background-color: rgb(234,244,255)', duration:0.1 });
			}
			else {
				element = document.getElementById("phUploadView - " + phUploads[i]);
				new Effect.Morph(element, { style:'background-color: rgb(255,255,255)', duration:0.1 });
			}
		}
	}

	function createFileInput() {
		if (document.forms["phUploadDropZone"].dropFile.value != "") {		
			fileElement = document.forms["phUploadDropZone"].dropFile.cloneNode(true);
		}
		else {
			fileElement = document.forms["phUploadDropZone"].addFile.cloneNode(true);
		}
		
		document.forms["phUploadDropZone"].reset();
		
		fileElement.setAttribute("name","file");
		fileName = fileElement.value;
		
		//if (shouldDenyExtension(fileName)) { }
				
		var fileArea = document.getElementById('phUploadFiles');
		var uploadUUID = new UUID();
		
		// Create form element for upload
		
		var newForm = document.createElement("form");
		
		newForm.setAttribute("id","phUploadForm - " + uploadUUID);
		newForm.setAttribute("action",phAppServerRoot + "/PHOTON/handlers/phUpload.cgi?UUID=" + uploadUUID);
		newForm.setAttribute("target","phUploadFrame");
		newForm.setAttribute("method","post");
		newForm.setAttribute("enctype","multipart/form-data");
		newForm.setAttribute("class","phUpload");

		fileArea.appendChild(newForm);
		
		fileElement.setAttribute("class","file hidden");
		
		document.forms["phUploadForm - " + uploadUUID].appendChild(fileElement);

		// Create view for file	

		drawViewForFileForm(newForm,null,null);

		phUploads.push(uploadUUID);

		phUploadCount++;
		
	}
	
	function checkFileExtentions(form){
		if(check_file_extentions == false){ return false; }
		var re = /(\.php)|(\.sh)$/i;   //Change line 126 in uber_uploader.cgi to match
		if(form['uploadedFile'].value != ""){
			if(form['uploadedFile'].value.match(re)){
				var string = form['uploadedFile'].value;
				var num_of_last_slash = string.lastIndexOf("\\");
				if(num_of_last_slash < 1){ num_of_last_slash = string.lastIndexOf("/"); }
				var file_name = string.slice(num_of_last_slash + 1, string.length);
				var file_extention = file_name.slice(file_name.indexOf(".")).toLowerCase(); 

				alert('Sorry, uploading a file with the extention "' + file_extention + '" is not allowed.');

				return true;
			}
		}
		return false;
	}	

//** FUNCTION: Process Queued Uploads
	function upload(userClicked) {				

		if (phUploads.length>0) {
			form = document.getElementById("phUploadForm - " + phUploads[0]);
			
			filename = form["file"].value;

			if (filename.lastIndexOf("\\")>0) {
				filename = filename.substring(filename.lastIndexOf("\\")+1,filename.length);
			}
			else if (filename.lastIndexOf("/")>0) {
				filename = filename.substring(filename.lastIndexOf("/")+1,filename.length);
			}

			//if (checkFileExtentions(form)) { return false; }

			makeRequest(phAppServerRoot + "/PHOTON/handlers/phUploadProgress.php?UUID="+ phUploads[0] +"&filename="+filename,progress);
			form.submit();
			
			new Effect.Fade(document.getElementById("phUploader_submit"), { from: 1.0, to: 0.5, duration: 0.1, queue: 'end' });

		}
		else {
			if (userClicked) {
				alert("No files were added to the upload. Add a file and try again.");
			}
		}
	}
	
	function addFile() {
		var uploadElement = document.getElementById("interact");
		var parentElement = document.getElementById("phUploadFiles");
		
		uploadElement.style.top = parentElement.scrollTop + "px";
		
		new Effect.Appear(uploadElement, { duration: 0.1, queue: 'end', afterFinish: function() {
			document.forms["phUploadDropZone"]["file"].browse();
		} });
	}
	
	
	function callbackReturn() {
		switch (http_request.readyState) {
			case 4 :
				if (http_request.status == 200) {
					eval(http_request.responseText);
				}
				else {
					alert(http_request.responseText);
				}
				break;
		}
		
	}
	
//** FUNCTION: Process Response From Ajax Request
	function progress() {
		
		switch (http_request.readyState) {
			case 4 :
				if (http_request.status == 200) {
					response = http_request.responseText.split('|');
					upload_form = document.getElementById("phUploadForm - " + phUploads[0]);
					
					switch(response[0]) {
						case "wait":
							drawViewForFileForm(upload_form,response[0],response);
							setTimeout('makeRequest("' + phAppServerRoot + '/PHOTON/handlers/phUploadProgress.php?UUID=' + phUploads[0] + '",progress);',1000);
							break;
						case "started":
							makeRequest(phAppServerRoot + "/PHOTON/handlers/phUploadProgress.php?UUID=" + phUploads[0],progress);
							break;
						case "downloading":
							drawViewForFileForm(upload_form,response[0],response);
							setTimeout('makeRequest("' + phAppServerRoot + '/PHOTON/handlers/phUploadProgress.php?UUID=' + phUploads[0] + '",progress);',1000);
							break;
						case "copying":
							drawViewForFileForm(upload_form,response[0],response);
							setTimeout('makeRequest("' + phAppServerRoot + '/PHOTON/handlers/phUploadProgress.php?UUID=' + phUploads[0] + '",progress);',1000);
							break;							
						case "Success":
							phUploads.splice(0,1);
							drawViewForFileForm(upload_form,response[0],response);
							
							new Effect.Appear(document.getElementById("phUploader_submit"), { from: 0.5, to: 1.0, duration: 0.1, queue: 'end' });
							setTimeout("upload();",1000);
							
							break;
						default:
							alert(http_request.responseText);
						
					}
				} else { alert("There was a problem with the request.("+http_request.responseText+")"); }
				break;
		}
	
	}

	function cancelUpload(msg) {				
		alert(msg);
	}
	
	
		
function drag_Drop(event) {
	var uploadZone = document.getElementById("interact");

	//new Effect.Morph(uploadElement, { style:'background: #ffffff; color: #cccccc; border-color: #cccccc', duration:0.1 });
	
	new Effect.Fade(uploadZone, { duration: 0.1, queue: 'end' });

	createFileInput();
}

function drag_Enter(event) {	
	if (!phUploadDrop) {
		phUploadDrop = true;

		var uploadElement = document.getElementById("interact");
		var parentElement = document.getElementById("phUploadFiles");
		
		uploadElement.style.top = parentElement.scrollTop + "px";
		
		new Effect.Appear(uploadElement, { duration: 0.1, queue: 'end' });
	}
	
}

function drag_Leave(event) {
	if (phUploadDrop) {
		phUploadDrop = false;

		var uploadElement = document.getElementById("interact");
		new Effect.Fade(uploadElement, { duration: 0.1, queue: 'end' });
	}
}