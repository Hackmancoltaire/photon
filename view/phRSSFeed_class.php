<?php

class phRSSFeed {

	var $title;
	var $link;
	var $description;
	var $language;
	var $copyright;
	var $webmaster;
	var $category;
	var $generator;
	var $docs;
	
	var $itemCount;
	var $items;
	
	function phRSSFeed($title = NULL) {
		$this->title = ($title) ? $title:"Generic Feed";
		$this->generator = "PHOTON RSS Generator";
		$this->docs = "http://blogs.law.harvard.edu/tech/rss/";
		
		$this->itemCount = 0;
		$this->items = array();
	}
	
	function addItem($item,$attributes) {
		if ((is_array($item) || is_object($item)) && $attributes != NULL) {
			$this->items[] = array(
				"item" => $item,
				"attributes" => $attributes
			);
		
			$this->itemCount++;
		}
	}
	
	function display($returnMode = "print") {
		$returnedText = "<?xml version='1.0' encoding='UTF-8' ?>
 		<rss version='2.0'><channel>\n";
	
		$returnedText .= "<title>$this->title</title>\n";
		if ($this->link) { $returnedText .= "<link>$this->link</link>\n"; }
		if ($this->description) { $returnedText .= "<description>$this->description</description>\n"; }
		if ($this->language) { $returnedText .= "<language>$this->language</language>\n"; }
		if ($this->copyright) { $returnedText .= "<copyright>$this->copyright</copyright>\n"; }
		
		// Date Format: Fri, 21 Jan 2005 13:15:00 PST
		$currentDate = date("D, j M Y G:i:s T");
		
		$returnedText .= "<pubDate>$currentDate</pubDate>\n";
		$returnedText .= "<lastBuildDate>$currentDate</lastBuildDate>\n";
		
		if ($this->category) { $returnedText .= "<category>$this->category</category>\n"; }
		if ($this->generator) { $returnedText .= "<generator>$this->generator</generator>\n"; }
		if ($this->docs) { $returnedText .= "<docs>$this->docs</docs>\n"; }
				
		for ($i=0; $i < $this->itemCount; $i++) {
			// Print out items.
			
			$itemAttributes = $this->items[$i][attributes];
			$item = "<item>\n";
			
			$attributeKeys = array_keys($itemAttributes);
			$attributeKeyCount = count($attributeKeys);
			
			for($a=0; $a < $attributeKeyCount; $a++) {
				if (is_array($this->items[$i][item])) {
					$itemValue = preg_replace("/\\%([a-zA-Z0-9()]+)\\%/e", '$this->items[$i][item][\1]',$this->items[$i][attributes][$attributeKeys[$a]]);
				}
				else if (is_object($this->items[$i][item])) {
					$itemValue = preg_replace("/\\%([a-zA-Z0-9()]+)\\%/e", '$this->items[$i][item]->\1',$this->items[$i][attributes][$attributeKeys[$a]]);
				}
				
				$item .= "<" . $attributeKeys[$a] . ">" . $itemValue . "</" . $attributeKeys[$a] . ">\n";
			}
			
			$item .= "</item>\n";
			$returnedText .= $item;
		}
		
		$returnedText .= "</channel></rss>";
		
		if ($returnMode == "print") { print $returnedText; }
		else { return $returnedText; }
	}
}

?>