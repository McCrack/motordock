<?php

$p = JSON::load('php://input');

require_once "core/api/".$p['resource']."/index.php";

/**
 * $p['api'];		- API Name for initialization
 * $p['command']	- API Command
 * $p['fields']		- Arguments
**/

$response = $p['resource']::{$p['api']}($p['command'])
	->post(function($client) use ($p){
		switch ($p['command']) {
			case "GetCategories":
				$client->query->CategoryParent = $p['fields'][0];
				$client->query->LevelLimit = 5;
				$client->query->CategorySiteID = 3;
				break;
			case "GetItem":
				$client->query->ItemID = $p['fields'][0];
				break;
			case "GetItemStatus":
			case "GetSingleItem":
			case "GetMultipleItems":
				foreach ($p['fields'] as $ItemID) {
					$client->query->addChild("ItemID", $ItemID);
				}
				break;
			case "GetUserProfile":
				$client->query->UserID = $UserID;
				break;
			case "GetStore":
				$client->query->UserID =  'scb-vehicledismantlers';
				break;
			case "GetCategoryInfo":
				$client->query->CategoryID = $CategoryID;
				break;
			case "findCompletedItems":

				break;
			default:
				break;
		}
});

if ($response['code']!=200) {
	print $response['code']."\n\n";
}
print_r($response['data']);

/*
* Import Categories
*
$categories = [];

$EN = new SimpleXMLElement($response->data);
foreach ($EN->CategoryArray->Category as $category) {
	if ((STRING)$category->CategoryLevel < 3) {
		continue;
	}
	$categories[(STRING)$category->CategoryID] = [
		"CatID"=>(STRING)$category->CategoryID,
		"ParentID"=>(STRING)$category->CategoryParentID,
		"NameEN"=>(STRING)$category->CategoryName,
		"Level"=>(STRING)$category->CategoryLevel,
		"RelID"=>"",
		"NameDE"=>"",
		"link"=>""
	];
}
$response = $p['resource']::{$p['api']}($p['command'])
	->post(function($client) use ($p){
		$client->query->CategoryParent = $p['fields'][0];
		$client->query->LevelLimit = 5;
		$client->query->CategorySiteID = 77;
});
$query = [];
$DE = new SimpleXMLElement($response->data);
foreach ($DE->CategoryArray->Category as $category) {
	if ((STRING)$category->CategoryLevel < 3) {
		continue;
	}

	$id = (STRING)$category->CategoryID;

	if (empty($categories[$id])) {
		print "ERROR: ".$id."/L: ".(STRING)$category->CategoryLevel."\n".(STRING)$category->CategoryName."\n\n";
	} else {
		$categories[$id]['RelID'] = $id;
		$categories[$id]['NameDE'] = (STRING)$category->CategoryName;
		$categories[$id]['link'] = translite((STRING)$category->CategoryName);

		print $id."/L: ".$categories[$id]['Level']."\n".$categories[$id]['NameEN']."\n".$categories[$id]['NameDE']."\n\n";

		$query[] = $GLOBALS['mySQL']->parse(
			"({int},{int},{int},{str},{str},{int},{str})",
			$categories[$id]['CatID'],
			$categories[$id]['ParentID'],
			$id,
			$categories[$id]['NameDE'],
			$categories[$id]['NameEN'],
			$categories[$id]['Level'],
			$categories[$id]['link']
		);

	}
}

print $GLOBALS['mySQL']->inquiry("
	INSERT INTO cb_categories
		(CatID, ParentID, RelID, NameDE, NameEN, Level, link)
	VALUES
		".implode(",", $query)
)['affected_rows']." - ".rand(0,100);
*/
