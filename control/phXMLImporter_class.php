<?php

// - PHOTON XML Importer Class. This file sets up the XML parser in PHP to
// properly handle Mac OX X plist files and convert them into a usable PHP
// keyed array. The parser handles infinite depth and keyed as well as non
// keyed information. The parser also caches parsed files so that it is not
// required to read and parse them a second time during the execution.

class phXMLImporter {

	var $objectDBConnection = array(
		"id"		=> array("phParsedXMLID",2)
	);

	var $id;					// This is the path to the file
	var $valid;

	var $xml;
	var $resultArray;

	var $currentKeyString;
	var $pointerTarget;
	var $pointerArray;
	var $isKeyed;
	var $keyString;
	var $shouldIgnore;
	
	var $xmlParser;
	var $parseError;

	function phXMLImporter($id) {
		$this->valid = false;
		$this->xmlParser = xml_parser_create();

		xml_set_object($this->xmlParser, $this);
		xml_parser_set_option($this->xmlParser, XML_OPTION_CASE_FOLDING, false);
		xml_set_element_handler($this->xmlParser, "startElement", "endElement");
		xml_set_character_data_handler($this->xmlParser, "characterData");
		
		if (!is_null($id)) {
			$this->id = $id;
			$this->parse();
		}
	}
	
	function setFilePath($id) { $this->id = $id; }
	
	function parse() {
		if (!is_null($this->id)) {
			// if this has already been parsed return the parsed object
			$objectMaster = new phObjectMaster;
	
			// If not restored then we need to process the object normally
			$restored = $objectMaster->restoreObjectWithID($this,$this->id);
	
			if ($restored) {
				$this->valid = true;
				return $restored;
			}
			else {
				// else parse on!		
				$this->xml = file_get_contents($this->id);
			
				if (!is_null($this->xml)) {
					$this->pointerArray = array(&$this->resultArray);
					$this->pointerTarget = &$this->pointerArray[count($this->pointerArray)-1];
					$this->currentKeyString = "";
					$this->isKeyed = false;
			
					$result = xml_parse($this->xmlParser, $this->xml);
					
					if ($result == 0) {
						$this->parseError = "XML error: " . xml_error_string(xml_get_error_code($this->xmlParser)) . " at line " . xml_get_current_line_number($this->xmlParser);
			
						xml_parser_free($this->xmlParser); // Release parser
						
						return FALSE;
					}
					else {
						xml_parser_free($this->xmlParser); // Release parser
						
						$objectMaster = new phObjectMaster;
						$stored = $objectMaster->storeObject($this);
						
						$this->valid = true;
						return $stored;
					}
				}
				else { return FALSE; }
			}
		}
		else { return FALSE; }
	}
	
	function getResults() { return $this->resultArray; }
	
	function startElement($parser, $name, $attrs) {
		if (!preg_match("/^\s+$/", $name)) {		
			if ($name == "key")	{ $this->keyString = true; }
			else if ($name == "array" || $name == "dict") {
				if ($this->isKeyed == true) {
					$this->pointerTarget[$this->currentKeyString] = array();
					$this->pointerArray[] = &$this->pointerTarget[$this->currentKeyString];
					$this->isKeyed = false;
				}
				else {
					$this->pointerTarget[] = array();
					$this->pointerArray[] = &$this->pointerTarget[count($this->pointerTarget)-1];
				}
				$this->pointerTarget = &$this->pointerArray[count($this->pointerArray)-1];
			}
			else {
				// The name is a string or int or something
			}
		}
		
		if ($name == "plist") { $this->shouldIgnore == true; }
		else { $this->shouldIgnore == false; }
	}
	
	function characterData($parser, $data) {		
		// If there is data to be parsed and we are not ignoring this attribute
		if ($data && $this->shouldIgnore == false) {
		
			// If this string is a key
			if ($this->keyString == true) {
				if (!preg_match("/\t+/", $data) && !preg_match("/\n+/", $data)) {
					$this->currentKeyString = $data;
					$this->isKeyed = true;
				}
			}
			else {
				if (!preg_match("/\t+/", $data) && !preg_match("/\n+/", $data)) {
					$this->dataStore .= utf8_decode($data);
				}
			}
		}
	}
	
	function endElement($parser, $name) {
		if ($name == "key" && $this->keyString == true) { $this->keyString = false; }
		else {
			if ($this->dataStore != NULL) {
				if ($this->isKeyed == true) {
					$this->pointerTarget[$this->currentKeyString] = $this->dataStore;
					$this->isKeyed = false;
				}
				else { $this->pointerTarget[] = $this->dataStore; }
				$this->dataStore = NULL;
			}
			else { if ($this->isKeyed == true) { $this->isKeyed = false; } }
		}
		if ($name == "dict" || $name == "array") {
			array_pop($this->pointerArray);
			$this->pointerTarget = &$this->pointerArray[count($this->pointerArray)-1];
		}
	}

}

?>