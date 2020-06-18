<?php

require_once("core.php");

$p = JSON::load('php://input');

switch(COMMAND){
	case "searching":
		$client = new searching("modules/ebay-import/config.init");
		$client->{$p['command']}();
		print JSON::encode($client->response);
	break;
	case "trading":
		exit;
		$ClientEN = new trading("modules/ebay-import/config.init");
		$ClientEN->{$p['command']}(6030,5,3);
		
		$EN = [];
		$DE = [];

		foreach($ClientEN->response->CategoryArray->Category as $itm){
			/*
			$EN[(INT)$itm->CategoryID] = [
				"NameEN"=>$itm->CategoryName,
				"CatID"=>$itm->CategoryID,
				"ParentID"=>$itm->CategoryParentID,
				"Level"=>$itm->CategoryLevel
			];
			*/
			if($itm->CategoryLevel>4) print print "[ ".$itm->CategoryID." ] ".$itm->CategoryName."\n";
		}
/*
		$ClientDE = new trading("modules/ebay-import/config.init");
		$ClientDE->{$p['command']}(6030,5,77);

		foreach($ClientDE->response->CategoryArray->Category as $itm){
			$DE[(INT)$itm->CategoryID] = [
				"NameDE"=>$itm->CategoryName,
				"CatID"=>$itm->CategoryID,
				"ParentID"=>$itm->CategoryParentID,
				"Level"=>$itm->CategoryLevel
			];
		}

		foreach($EN as $id=>$category){
			$GLOBALS['mySQL']->inquiry("INSERT INTO cb_categories (CatID,RelID,ParentID,Level,NameEN,NameDE) VALUES ({arr})", [
				$id,
				$id,
				$category['ParentID'],
				$category['Level'],
				$category['NameEN'],
				$DE[$id]['NameDE']
			]);
		}



		$TempEN = [];
		foreach($EN as $id=>$category){
			if(isset($DE[$id])){ // intersection of values
				print "[ ".$id." ] ".$category['NameEN']." / ".$DE[$id]['NameDE']."\n";
				unset($DE[$id]);
			}else{
				$TempEN[$id] = $category; 
			}
		}
		include_once("core/GoogleTranslate.php");
		$tr = new GoogleTranslateForFree();

		print "\n ******************************************* \n";
		foreach($TempEN as $id=>$category){
			$text = $category['NameEN'];
			print "[ ".$id." ] ".$category['NameEN']." / ".$tr->translate("en", "ru", $text, 5)."\n";
		}
		print "\n ******************************************* \n";
		foreach($DE as $id=>$category){
			$text = $category['NameDE'];
			print "[ ".$id." ] ".$category['NameDE']." / ".$tr->translate("de", "ru", $text, 5)."\n";
		}
*/
		/*
		$cats = $GLOBALS['mySQL']->getGroup("SELECT CatID FROM cb_categories")['CatID'];

		$values = [];
		foreach($client->response->CategoryArray->Category as $itm){
			
			if(in_array($itm->CategoryID, $cats)){
				$GLOBALS['mySQL']->inquiry("UPDATE cb_categories SET RelID=CatID WHERE CatID={int} LIMIT 1", (INT)$itm->CategoryID);
				
			}else print $itm->CategoryName." - Not Exists\n"; 
		}
		*/
	break;
	case "shopping":
		$client = new shopping("modules/ebay-import/config.init");
		$client->{$p['command']}();
		print $client->response->asXML();
	break;
	case "browse":
		$client = new browse("modules/ebay-import/config.init");
		$client->{$p['command']}(223383016742);
		print JSON::encode($client->response);
	break;
	case "translate":
		include_once("core/GoogleTranslate.php");
		
		$tr = new GoogleTranslate();
		$text = "FRONT GRILLE FORD A6 (C7) 2011 - 2014 Grille Front Centre Badge & WARRANTY.";

		$result = $tr->translate("en", "ru", $text, 5);
		//$result = $tr->translate("en", "de", ["hello", "world"], 5);

		print_r($result);
	break;
	default:break;
}

?>