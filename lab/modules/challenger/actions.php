<?php
switch(COMMAND){
	case "gt_fragment":
		$RAW_POST_DATA = file_get_contents('php://input');
		$query = preg_replace("/\s/", "+", $RAW_POST_DATA);
		$fragment = file_get_contents($query);
		print($fragment);
	break;
	case "confirm_list":
		$p = JSON::load('php://input');
		$missing = [];
		foreach($p as $itm){
			$itm['id'] = (INT)$itm['id'];
			$id = (INT)$GLOBALS['mySQL']->inquiry("INSERT INTO gb_silverlake SET {set}", [
				"LakeID"=>$itm['id'],
				"preview"=>$itm['preview'],
				"price"=>$itm['price'],
				"state"=>$itm['state']
			])['last_id'];

			if($id){
				$missing[] = [
					"id"=>$id,
					"reference"=>$itm['reference']
				];
			}else $GLOBALS['mySQL']->inquiry("UPDATE gb_silverlake SET price={str} WHERE LakeID={int} LIMIT 1",$itm['price'], $itm['id']);
		}

		print JSON::encode($missing);
	break;
	case "ad_part":
		$p = JSON::load('php://input');

		$PartID = $GLOBALS['mySQL']->getRow("SELECT PartID FROM gb_parts WHERE RefID={int}",$p['RefID'])['PartID'];
		if($PartID){
			$GLOBALS['mySQL']->inquiry("UPDATE gb_parts SET added={int} WHERE PartID={int}",time(),$PartID);
			exit("Allready Existst");
		}else{
			$category = translite($p['category']);

			$CatID = $GLOBALS['mySQL']->getRow("SELECT CatID FROM gb_categories WHERE category LIKE {str} LIMIT 1", $category)['CatID'];
			if(empty($CatID)){
				$CatID = $GLOBALS['mySQL']->inquiry("
					INSERT INTO gb_categories (indx,category,alias,PageID) VALUES ({str},{str},{str},36)",
					strtoupper($p['category'][0]),
					$p['category'],
					$category
				)['last_id'];
			}

			$PartID = $GLOBALS['mySQL']->inquiry("INSERT INTO gb_parts (LineID,RefID,CatID,Reference,named,preview,price,added) VALUES ({arr})",[
				$p['LineID'],
				$p['RefID'],
				$CatID,
				$p['Reference'],
				$p['named'],
				$p['preview'],
				$p['price'],
				time()
			])['last_id'];

			if($PartID){
				foreach($p['optionset'] as $key=>&$val) $val = base64_decode($val);

				$GLOBALS['mySQL']->inquiry("UPDATE gb_parts SET imageset={str},optionset={str} WHERE PartID={int}",
				JSON::encode($p['imageset']),
				JSON::encode($p['optionset']),
				$PartID);

				print "Added";
			}else exit("Fail");
		}
	break;
	case "gt_lineups":
		$lineups = $GLOBALS['mySQL']->get("SELECT LineID,Lineup,Model,published,last_update FROM gb_lineups WHERE LabelID = {int}", ARG_1)?>
		<div class="lineups">
			<?foreach($lineups as $lineup):?>
			<label>
				<input name="lineup" <?if($lineup['published']=="Published"):?>checked<?endif?> data-lineup="<?=$lineup['Lineup']?>" value="<?=$lineup['Model']?>" data-id="<?=$lineup['LineID']?>" type="checkbox">
				<span><?=$lineup['Model']?></span>
			</label>
			<?endforeach?>
		</div>
	<?break;
	case "gt_parts":
		$stamp = time();
		//$GLOBALS['mySQL']->inquiry("DELETE FROM gb_parts WHERE LineID LIKE {int} AND added < {int}", ARG_1, ($stamp - 604800)); // 7 Days Old
		//$GLOBALS['mySQL']->inquiry("DELETE FROM gb_parts WHERE LineID LIKE {int} AND added < {int}", ARG_1, ($stamp - 1209600)); // 14 Days Old
		$GLOBALS['mySQL']->inquiry("UPDATE gb_lineups SET last_update={int} WHERE LineID LIKE {int}", $stamp, ARG_1);

		$parts = $GLOBALS['mySQL']->getGroup("SELECT PartID,RefID,Reference FROM gb_parts WHERE LineID LIKE {int}", ARG_1);

		print is_array($parts) ? JSON::encode($parts) : "[]";
	break;
	/*~~~~~*/
	case "ad_model":
		$p = JSON::load('php://input');
		print $GLOBALS['mySQL']->inquiry("INSERT INTO gb_lineups (LabelID,Lineup,Model,published,image) VALUES ({arr})", [
			$p['LabelID'],
			$p['Lineup'],
			$p['Model'],
			$p['published'],
			$p['image']
		])['last_id'];
	break;
	case "ch_mark":
		$p = JSON::load('php://input');
		$field = key($p);
		$set = $GLOBALS['mySQL']->parse("{set}",[$field=>$p[$field]]);
		print $GLOBALS['mySQL']->inquiry("UPDATE gb_lineups SET {prp} WHERE LineID={int} LIMIT 1", $set,ARG_1)['affected_rows'];
	break;
	case "gt_mark":
		$mark = $GLOBALS['mySQL']->get("SELECT LineID,Lineup FROM gb_lineups WHERE LabelID = {int} GROUP BY Lineup", ARG_1);
		foreach($mark as $lineup):?>
		<label>
			<input name="lineups" value="<?=$lineup['Lineup']?>" data-id="<?=$lineup['LineID']?>" type="radio" hidden>
			<span><?=$lineup['Lineup']?></span>
		</label>
		<?endforeach;
	break;
	case "gt_models":
		$models = $GLOBALS['mySQL']->get("SELECT LineID,Model FROM gb_lineups WHERE LabelID = {int} AND Lineup LIKE {str}", ARG_1,ARG_2);
		foreach($models as $model):?>
		<label>
			<input name="model" value="<?=$model['Model']?>" data-id="<?=$model['LineID']?>" type="radio" hidden>
			<span><?=$model['Model']?></span>
		</label>
		<?endforeach;
	break;
	case "gt_model":
		$lineup = $GLOBALS['mySQL']->getRow("SELECT * FROM gb_lineups WHERE LineID = {int} LIMIT 1", ARG_1);
		$lineup['last_update'] = date('d M, H:i', $lineup['last_update']);
		print JSON::encode($lineup);
	break;
	case "rm_model":
		print $GLOBALS['mySQL']->inquiry("DELETE FROM gb_lineups WHERE LineID = {int} LIMIT 1", ARG_1)['affected_rows'];
	break;
	/*~~~~~*/
	case "ch_alias":
		$RAW_POST_DATA = file_get_contents('php://input');
		print $GLOBALS['mySQL']->inquiry("UPDATE gb_categories SET alias = {str} WHERE CatID = {int} LIMIT 1", $RAW_POST_DATA,ARG_1)['affected_rows'];
	break;
	case "ch_part":
		print $GLOBALS['mySQL']->inquiry("UPDATE gb_categories SET PageID = {int} WHERE CatID = {int} LIMIT 1", ARG_2,ARG_1)['affected_rows'];
	break;
	case "gt_part":

		$parts = $GLOBALS['mySQL']->get("SELECT PageID,header FROM gb_sitemap WHERE parent LIKE 'showcase' ORDER BY SortID ASC");

		foreach($GLOBALS['mySQL']->get("SELECT * FROM gb_categories WHERE PageID={int}", ARG_1) as $cat):?>
		<tr data-id="<?=$cat['CatID']?>">
			<td align="center"><?=$cat['CatID']?></td>
			<td><?=$cat['category']?></td>
			<td contenteditable="true"><?=$cat['alias']?></td>
			<td>
				<div class="select">
					<select name="part" class="black-txt" data-id="<?=$cat['CatID']?>">
						<?foreach($parts as $part):?>
						<option <?if($part['PageID']==$cat['PageID']):?>selected<?endif?> value="<?=$part['PageID']?>"><?=$part['header']?></option>
						<?endforeach?>
					</select>
				</div>
			</td>
		</tr>
		<?endforeach;
	break;
	case "rm_part":
		print $GLOBALS['mySQL']->inquiry("DELETE FROM gb_parts WHERE PartID = {int} LIMIT 1", ARG_1)['affected_rows'];
	break;
	default:break;
}
?>
