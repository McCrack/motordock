<?php

global $mySQL;

switch(COMMAND){
	case "ad_item":
		print $mySQL->inquiry("INSERT INTO gb_parts SET {set}, added={int}", JSON::load('php://input'), time())['last_id'];
	break;

	case "sv_part":
		$RAW_POST_DATA = file_get_contents('php://input');
		print $mySQL->inquiry("UPDATE gb_parts SET {fld}={str} WHERE PartID={int} LIMIT 1", ARG_2, base64_decode($RAW_POST_DATA), ARG_1)['affected_rows'];
	break;
	case "sv_options":
		$p = JSON::load('php://input');
		foreach($p as $key=>&$val) $val = base64_decode($val);
		print $mySQL->inquiry("UPDATE gb_parts SET optionset={str} WHERE PartID = {int} LIMIT 1", JSON::encode($p),ARG_1)['affected_rows'];
	break;
	case "sv_imageset":
		$data = file_get_contents('php://input');
		print $mySQL->inquiry("UPDATE gb_parts SET imageset={str} WHERE PartID = {int}", $data,ARG_1)['affected_rows'];
	break;
	case "gt_lineups":?>
		<option selected disabled>Choose a Lineup</option>
		<?foreach($mySQL->get("SELECT LineID,Model FROM gb_lineups WHERE LabelID={int}", ARG_1) as $lineup):?>
		<option value="<?=$lineup['LineID']?>"><?=$lineup['Model']?></option>
		<?endforeach;
	break;
	case "rm_part":
		print $mySQL->inquiry("DELETE FROM gb_parts WHERE PartID={int}" ,ARG_1)['affected_rows'];
	break;
	default:break;
}

?>
