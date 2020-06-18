<?php


require "../vendor/autoload.php";

$config = new Config();
define(
	"BASE_FOLDER",
	$_COOKIE['subdomain'] ?? $config->{"base folder"}
);

$sConfig = new Config("../".BASE_FOLDER."/".$config->{"config file"});

$mySQL = new db(
  $sConfig->{"db host"},
  $sConfig->{"db user"},
  $sConfig->{"db password"},
  $sConfig->{"db name"}
);
$mySQL->set_charset("utf8");

require_once "../core/login.php";

define(
	"DEFAULT_LANG",
	$config->{"language"}
);
define(
	"USER_LANG",
  $uConfig->language ?? DEFAULT_LANG
);

$host = explode(".", $_SERVER['HTTP_HOST']);
define(
	"HOST",
	implode(".", array_slice($host, 1))
);
define(
	"PROTOCOL",
  getProtocol()
);

$params = preg_split(
	"/\//",
	urldecode($_GET['params']),
	-1,
	PREG_SPLIT_NO_EMPTY
);

define(
	"SECTION",
	empty($params[0])
		? $config->{"default module"}
		: array_shift($params)
);

if (file_exists("modules/".SECTION."/config.init")) {
    $conf = JSON::load("modules/".SECTION."/config.init");
    $groups = preg_split("/,\s*/", $conf['access']['value'], -1, PREG_SPLIT_NO_EMPTY);
    if (defined("USER_ID") && in_array(USER_GROUP, $groups) || "YES"==($conf['free access']['value'] ?? false)) {

        $path = "modules/".SECTION;
        if (is_dir($path."/".current($params))) {
          $path .= "/".array_shift($params);
        }
        foreach ($params as $i=>$itm) {
            define("ARG_".($i+1), $itm);
        }
        if (defined("ARG_1") && file_exists($path."/".ARG_1.".php")) {
            include_once $path."/".ARG_1.".php";
        } elseif(file_exists($path."/index.php")) {
            include_once $path."/index.php";
        } elseif(file_exists("modules/".SECTION."/index.php")) {
            include_once "modules/".SECTION."/index.php";
        } else {
            header("Location: /".$config->{"default module"});
        }
    } else {
        die(include_once "login.php" );
    }

} else {
    header("Location: /".$config->{"default module"});
}

$mySQL->close();

?>