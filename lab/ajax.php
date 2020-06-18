<?php

require "../vendor/autoload.php";

$params = preg_split("/\//", urldecode($_GET['params']), -1, PREG_SPLIT_NO_EMPTY);
define("SECTION", empty($params[0]) ? $config->{"default module"} : array_shift($params));

$path = "ajax/".SECTION;

if (is_dir($path."/".current($params))) {
	$path .= "/".array_shift($params);
}
foreach ($params as $i=>$itm) {
	define("ARG_".($i+1), $itm);
}

if(file_exists($path."/".ARG_1.".php")){
	include_once $path."/".ARG_1.".php";
}elseif(file_exists($path."/index.php")){
	include_once $path."/index.php";
}elseif(file_exists("ajax/".SECTION."/index.php")){
	include_once "ajax/".SECTION."/index.php";
}else exit("Not Found");

?>
