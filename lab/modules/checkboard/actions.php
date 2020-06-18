<?php

switch(ARG_2){
	case "checked":
		$p = JSON::load('php://input');
		$board = JSON::load("patterns/json/checkboard.json");

		$board[$p['board']][$p['item']]['status'] = $p['status'];
		$board[$p['board']][$p['item']]['log'][] = USER_LOGIN.", ".$p['status']."  [".date("d M, H:i")."]";
		JSON::save("patterns/json/checkboard.json", JSON::encode($board));
	break;
	case "create-board":
		$caption = file_get_contents('php://input');
		$board = JSON::load("patterns/json/checkboard.json");
		if(empty($board[$caption])){
			$board[$caption] = [];
			JSON::save("patterns/json/checkboard.json", JSON::encode($board));
		}?>
		<fieldset>
			<legend class="h-bar t red-txt">
				<span class="active-txt"><?=$caption?></span>
				<label class="tool" title="add item" onclick="addItemToBoard('<?=$caption?>',this.parent(2))">&#xe146;</label>
			</legend>
		</fieldset><hr>
		<?
	break;
	case "add-item":
		$p = JSON::load('php://input');
		$board = JSON::load("patterns/json/checkboard.json");
		$log = USER_LOGIN.", Added [".date("d M, H:i")."]";
		$board[$p['board']][] = [
			"caption"=>$p['task'],
			"status"=>"enabled",
			"log"=>[$log]
		];
		JSON::save("patterns/json/checkboard.json", JSON::encode($board));
		?>
		<label class="enabled" title="<?=$p['task']?>">
			<i>ℹ</i>
			<div class="log">
				<div class="red-txt"><?=$p['task']?></div>
					<div class="gold-txt"><?=$log?></div>
				</div>
		</label>
		<?
	break;
	default:break;
}
?>