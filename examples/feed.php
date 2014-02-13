<?php
	include "./common/magicDance.php";
	
	if ($action == "search") {
		$rssFeed = new rssFeed("HotAds: Search Results");
		$rssFeed->link = "http://esso.apple.com/hotads2k5/search.php?action=search&criteria=$criteria&areaCode=$areaCode&categoryCode=$categoryCode";
	
		$mySearchTerms = seperateTerms(stripSlashes($criteria));
		
		if ($mySearchTerms[email]) {
			$email = $mySearchTerms[email];
			
			$mySearchTerms[email] = NULL;
			unset($mySearchTerms[email]);
		}
		
		if (count($mySearchTerms) > 0) { $searchCriteria[] = "(" . produceSearchTerm("ad.message",$mySearchTerms) . " OR " . produceSearchTerm("ad.subject",$mySearchTerms) . ")"; }
		if ($areaCode && $areaCode != "null") { $searchCriteria[] = "areaCode = $areaCode"; }
		if ($categoryCode && $categoryCode != "null") { $searchCriteria[] = "categoryCode = $categoryCode"; }
		
		if ($email) {
			$query = "SELECT DISTINCT user_ad_join.adID FROM user_ad_join,users LEFT JOIN ad ON user_ad_join.adID = ad.adID";
			$searchCriteria[] = "ad.adID = user_ad_join.adID";
			$searchCriteria[] = "users.appledsID = user_ad_join.appledsID";
			$searchCriteria[] = "users.email = \"$email\" OR ad.userEmail = \"$email\"";
		}
		else { $query = "SELECT adID FROM ad"; }

		if (count($searchCriteria) > 0) {
			$query .= " WHERE ";
			$query .= implode(" AND ",$searchCriteria);
		}
		
		//print $query; // Uncomment to see the query sent to the database.

		connect();
		$result = mysql_db_query($targetDatabase,$query);
			
		if (!$result) { error(); }
		else {
			$row = mysql_num_rows($result);
			
			if ($row > 0) {
				while ($row = mysql_fetch_array($result)) {
					$ad = new Ad($row[adID]);
					
					if ($ad->valid) {
						$rssFeed->addItem($ad,array(
							title => "%headline%",
							"content:encoded" => "<![CDATA[%preparedMessage()%]]>",
							author => "%userName%",
							link => "http://esso.apple.com/hotads2k5/ad.php?adID=%id%",
							pubDate => date("D, j M Y G:i:s T",$ad->creationDate)
						));
					}
				}
			}
			else { $adTable->addRow(array("There were no results for your search. Try broadening your search criteria.%span")); }
		}
		disconnect();
		
		$rssFeed->display();
	}
	else {	
		$limit = 20;
		
		$rssFeed = new rssFeed("HotAds: $limit Newest Ads");
		$rssFeed->link = "http://esso.apple.com/hotads2k5";
		
		connect();
		$result = mysql_db_query($targetDatabase,"SELECT adID FROM ad WHERE deleteMark IS NULL ORDER BY postdate DESC LIMIT $limit");

		if (!$result) { error(); }
		else {
			$row = mysql_num_rows($result);
			
			if ($row > 0) {
				while ($row = mysql_fetch_array($result)) {
					$ad = new Ad($row[adID]);
					
					if ($ad->valid) {
						$rssFeed->addItem($ad,array(
							title => "%headline%",
							"content:encoded" => "<![CDATA[%preparedMessage()%]]>",
							author => "%userName%",
							link => "http://esso.apple.com/hotads2k5/ad.php?adID=%id%",
							pubDate => date("D, j M Y G:i:s T",$ad->creationDate)
						));
					}
				}
			}
		}
		disconnect();
	
		$rssFeed->display();
	}
?>
