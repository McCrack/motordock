<?php

global $mySQL;

switch (COMMAND) {
	case "ad_model":
		$p = JSON::load('php://input');
		$time = time();

		$PageID = $mySQL->inquiry("INSERT INTO gb_pages SET {set}",[
			"type"=>"showcase",
			"created"=>$time,
			"modified"=>$time
		])['last_id'];

		$ItemID = $mySQL->inquiry("INSERT INTO gb_items SET PageID={int}",$PageID)['last_id'];

		$mySQL->inquiry("INSERT INTO gb_models SET {set}",[
			"PageID"=>$PageID,
			"ItemID"=>$ItemID,
			"CategoryID"=>$p['category'],
			"named"=>$p['name']
		]);
		/*
		$mySQL->inquiry("INSERT INTO language_vs_pages SET {set}",[
			"PageID"=>$PageID,
			"language"=>$p['language'],
			"LanguageID"=>?
		]);
		*/
		print $PageID."/".$ItemID;
		break;
	case "rl_tree":
		$rows = $mySQL->getTree(
			"name",
			"parent",
			"SELECT
				parent,
				name,
				header,
				language,
				published
			FROM
				gb_sitemap
			WHERE
				language LIKE {str}
			ORDER BY
				SortID ASC",
			ARG_1
		);
		print staticTree($rows);
		break;
	case "rl_list":
		$rows = $mySQL->getTree(
			"name",
			"parent",
			"SELECT
				PageID,
				name,
				parent,
				header
			FROM
				gb_sitemap
			WHERE
				language LIKE {str}",
			ARG_1
		);
		print catlist($rows);
		break;
	case "sv_filterset":
		$p = JSON::load('php://input');
		$filterset = [];
		foreach ($p as $setname=>$set) {
			$filterset[$setname] = [];
			foreach ($set as $key=>$value) {
				$id = $mySQL->getRow(
					"SELECT FilterID FROM gb_filters WHERE value LIKE {str}",
					$key
				)['FilterID'];
				if (empty($id)) {
					$id = $mySQL->inquiry(
						"INSERT INTO gb_filters SET {set}",
						[
							"value"=>$key,
							"caption"=>$value
						]
					)['last_id'];
				}
				$filterset[$setname][$id] = $value;
			}
		}
		$mySQL->inquiry(
			"UPDATE gb_static SET filterset={str} WHERE PageID={int} LIMIT 1",
			JSON::encode($filterset),
			ARG_1
		);
		break;
	case "sv_optionset":
		$RAW_POST_DATA = file_get_contents('php://input');
		$mySQL->inquiry(
			"UPDATE gb_static SET optionset={str} WHERE PageID={int} LIMIT 1",
			$RAW_POST_DATA,
			ARG_1
		);
		break;
	/*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*~*/
	case "ad_item":
		$model = $mySQL->getRow(
			"SELECT
				*
			FROM
				gb_models
			CROSS JOIN
				gb_items USING(ItemID)
			WHERE
				gb_models.PageID={int}
			LIMIT 1",
			ARG_1
		);

		if (empty($model)) {
			$ItemID =  $mySQL->inquiry(
				"INSERT INTO gb_items SET PageID={int}",
				ARG_1
			);
			$mySQL->inquiry(
				"UPDATE gb_models SET ItemID={int} WHERE PageID={int} LIMIT 1",
				$ItemID,
				ARG_1
			);
			print $ItemID;
		} else {
			print $mySQL->inquiry(
				"INSERT INTO gb_items SET {set}",
				[
					"PageID"=>ARG_1,
					"purchase"=>$model['purchase'],
					"selling"=>$model['selling'],
					"outstock"=>$model['outstock']
				]
			)['last_id'];
		}
		$mySQL->inquiry(
			"UPDATE gb_pages SET modified={int} WHERE PageID={int} LIMIT 1",
			time(),
			ARG_1
		);
		break;
	case "ch_model":
		$RAW_POST_DATA = file_get_contents('php://input');
		print $mySQL->inquiry(
			"UPDATE gb_models SET {fld}={str} WHERE PageID={int}",
			ARG_2,
			base64_decode($RAW_POST_DATA),
			ARG_1
		)['affected_rows'];
		$mySQL->inquiry(
			"UPDATE gb_pages SET modified={int} WHERE PageID={int} LIMIT 1",
			time(),
			ARG_1
		);
		break;
	case "ch_item":
		$RAW_POST_DATA = file_get_contents('php://input');
		print $mySQL->inquiry(
			"UPDATE gb_items SET {fld}={str} WHERE ItemID={int}",
			ARG_2,
			base64_decode($RAW_POST_DATA),
			ARG_1
		)['affected_rows'];
		$mySQL->inquiry(
			"UPDATE gb_pages SET modified={int} WHERE PageID={int} LIMIT 1",
			time(),
			ARG_1
		);
	break;
	case "st_label":
		print $mySQL->inquiry(
			"UPDATE gb_models SET LabelID={int} WHERE PageID={int}",
			ARG_2,
			ARG_1
		)['affected_rows'];
		$mySQL->inquiry(
			"UPDATE gb_pages SET modified={int} WHERE PageID={int} LIMIT 1",
			time(),
			ARG_1
		);
		break;
	case "ad_discount":
		$time = time();
		print $mySQL->inquiry(
			"INSERT INTO gb_discounts SET begining={int},ended={int}",
			$time,
			$time
		)['last_id'];
		$mySQL->inquiry(
			"UPDATE gb_pages SET modified={int} WHERE PageID={int} LIMIT 1",
			time(),
			ARG_1
		);
	break;
	case "gt_discount":
		$discount = $mySQL->getRow(
			"SELECT * FROM gb_discounts WHERE DiscountID={int} LIMIT 1",
			ARG_1
		)?>
		<div align="left">
			<input type="checkbox" name="saved" hidden>
			<output value="<?=$discount['DiscountID']?>">Discount ID: </output>
			<input name="DiscountID" value="<?=$discount['DiscountID']?>" type="hidden">
		</div>
		<div class="left" style="white-space:nowrap;">
			<input name="sticker" value="<?=$discount['sticker']?>" class="text-field" placeholder="sticker" data-translate="placeholder" size="10">
			<input name="discount" value="<?=$discount['discount']?>" class="text-field" placeholder="value" data-translate="placeholder" size="8">
		</div>
		<span class="tool">&#xe900;</span>
		<input required name="begining" type="date" value="<?=date("Y-m-d", $discount['begining'])?>" class="text-field" size="9">
		<input required name="ended" type="date" value="<?=date("Y-m-d", $discount['ended'])?>" class="text-field" size="9">

		<input name="caption" value="<?=$discount['caption']?>" class="text-field" placeholder="named" data-translate="placeholder">
		<textarea name="essence" class="text-field" placeholder="description" data-translate="placeholder"><?=$discount['essence']?></textarea>
	<?
		break;
	case "ch_discount":
		$RAW_POST_DATA = file_get_contents('php://input');
		print $mySQL->inquiry(
			"UPDATE gb_discounts SET {fld}={str} WHERE DiscountID={int}",
			ARG_2,
			base64_decode($RAW_POST_DATA),
			ARG_1
		)['affected_rows'];
	break;
	case "dl_discount":
		print $mySQL->inquiry(
			"DELETE FROM gb_discounts WHERE DiscountID={int}",
			ARG_1
		)['affected_rows'];
	break;
	case "st_remainders":
		$p = JSON::load('php://input');
		foreach($p as $stock=>$remainder){
			$mySQL->inquiry(
				"INSERT INTO gb_stock SET {set} ON DUPLICATE KEY UPDATE remainder={int}",
				[
					"ItemID"=>ARG_1,
					"stock"=>$stock,
					"remainder"=>$remainder
				],
				$remainder
			);
			$total += $remainder;
		}
		print($total);
		break;
	case "st_filter":
		print $mySQL->inquiry(
			"INSERT INTO item_vs_filters SET PageID = {int}, FilterID = {int}",
			ARG_1,
			ARG_2
		)['affected_rows'];
		break;
	case "dp_filter":
		print $mySQL->inquiry(
			"DELETE FROM item_vs_filters WHERE PageID = {int} AND FilterID = {int}",
			ARG_1,
			ARG_2
		)['affected_rows'];
		break;
	case "sv_options":
		$RAW_POST_DATA = file_get_contents('php://input');
		print $mySQL->inquiry(
			"UPDATE gb_models SET options={str} WHERE PageID = {int}",
			$RAW_POST_DATA,
			ARG_1
		)['affected_rows'];
		$mySQL->inquiry(
			"UPDATE gb_pages SET modified={int} WHERE PageID={int} LIMIT 1",
			time(),
			ARG_1
		);
		break;
	case "sv_description":
		$RAW_POST_DATA = file_get_contents('php://input');
		print $mySQL->inquiry(
			"UPDATE gb_models SET description={str} WHERE PageID = {int}",
			gzencode($RAW_POST_DATA),
			ARG_1
		)['affected_rows'];

		$mySQL->inquiry(
			"UPDATE gb_pages SET modified={int} WHERE PageID={int} LIMIT 1",
			time(),
			ARG_1
		);
		break;
	case "sv_images":
		$RAW_POST_DATA = file_get_contents('php://input');
		print $mySQL->inquiry(
			"UPDATE gb_models SET mediaset={str} WHERE PageID = {int}",
			$RAW_POST_DATA,
			ARG_1
		)['affected_rows'];
		break;
	default:
		break;
}

/***************************************************************************/

function staticTree(&$items, $offset="showcase")
{
	if (is_array($items[$offset])):?>
	<div class="root">
	<?foreach ($items[$offset] as $key=>$val):?>
		<a href="/showcase/<?=($val['language'].'/'.$val['name'])?>" class="<?if($val['published']==='Published'):?>published-txt<?endif?>"><?=(empty($val['header'])?$val['name']:$val['header'])?></a>
		<?staticTree($items, $key, $part);
	endforeach?>
	</div>
	<?endif;
}

function catlist(&$items, $offset="showcase")
{
	if (is_array($items[$offset])):?>
		<?foreach($items[$offset] as $key=>$val):?>
		<option <?if($val['PageID']==ARG_3):?>selected<?endif?> value="<?=$val['PageID']?>"><?=(empty($val['header'])?$val['name']:$val['header'])?></option>
		<?catlist($items, $key);
		endforeach?>
	<?endif;
}

?>
