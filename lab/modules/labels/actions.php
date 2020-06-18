<?php
switch(ARG_2){
	case "add-label":
		print $mySQL->inquiry("INSERT INTO cb_brands () VALUES ()")['last_id'];
	break;
	case "change":
		$p = JSON::load('php://input');
		$field = key($p);
		if($field=="named"){
			$set = $mySQL->parse("{set}",[
				"idx"=>$p['named'][0],
				"barnd"=>$p['named'],
				"slug"=>translite($p['named'])
			]);
		}else $set = $mySQL->parse("{set}",[$field=>$p[$field]]);
		print $mySQL->inquiry("UPDATE cb_brands SET {prp} WHERE BrandID={int} LIMIT 1", $set,ARG_3)['affected_rows'];
	break;
	case "delete":
		print $mySQL->inquiry("DELETE FROM cb_brands WHERE BrandID = {int} LIMIT 1", ARG_3)['affected_rows'];
	break;
	case "reload":
		$rows = $mySQL->get("SELECT * FROM cb_brands WHERE idx LIKE {str}", ARG_3);
		foreach($rows as $row):?>
		<tr data-id="<?=$row['BrandID']?>">
			<td><?=$row['BrandID']?></td>
			<td><?=$row['brand']?></td>
			<th class="tool drop-row" title="delete service" data-translate="title">✕</th>
		</tr>
		<?endforeach;
	break;
	case "get-label":
		$brand = $mySQL->getRow("SELECT * FROM cb_brands WHERE BrandID = {int} LIMIT 1", ARG_3)?>
		
		<output name="BrandID" class="dark-txt"><?=$brand['BrandID']?></output>
		<label><input name="favorite" <?if($brand['favorite']):?>checked<?endif?> value="1" type="checkbox"> <span>Favorite</span></label>
		
		<input name="brand" value="<?=$brand['brand']?>" placeholder="Named">
		<input name="idx" value="<?=$brand['idx']?>">
		<img src="<?=$brand['logo']?>" alt="" vspace="8">
	<?break;
	default: break;
}
?>