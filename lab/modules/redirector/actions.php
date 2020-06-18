<?php

switch(ARG_2){
	case "add-record":
		print $mySQL->inquiry("INSERT INTO gb_redirects () VALUES ()")['last_id'];
	break;
	case "delete":
		print $mySQL->inquiry("DELETE FROM gb_redirects WHERE ID={int}", ARG_3)['affected_rows'];
	break;
	case "change":
		$p = JSON::load('php://input');
		$field = key($p);

		$set = [$field=>$p[$field]];
		if($field=="source") $set['hash'] = md5($p[$field]);
		print $mySQL->inquiry("UPDATE gb_redirects SET {prp} WHERE ID={int} LIMIT 1", $mySQL->parse("{set}",$set), ARG_3)['affected_rows'];
	break;
	default:break;
}
?>