<?php

/* CONFIG *************************************************************************************************************************************/

class Config
{
	public $list = [];
	public function __construct($path = "config.init")
	{
		if (is_file($path)) {
				$json = file_get_contents($path);
				$this->list = json_decode($json, true) OR exit("<b>ERROR:</b> config file not found.");
		} else {
			$this->list = json_decode($path, true);
		}
		foreach ($this->list as $section=>$items) {
			foreach ($items as $key=>$val) {
				$this->{$key} = $val['value'] ?? $val;
			}
		}
		$this->languageset = $this->list['general']['language']['valid'] ?? [];
	}
	public function __get($key)
	{
		return $this->list[$key] ?? null;
	}
}

/* FS *****************************************************************************************************************************************/

function copyFolder($source, $dest)
{
	foreach (scandir($source) as $file) {
		if (($file!="." && $file!="..") && is_dir($source."/".$file)) {
			mkpath($dest."/".$file);
			copyFolder($source."/".$file, $dest."/".$file);
		} elseif (is_file($source."/".$file)) {
			@mkpath( $dest );
			copy($source."/".$file, $dest."/".$file);
		}
	}
}
function mkpath($path)
{
	$step=array();
	foreach (explode("/", $path) as $item) {
		$step[]=$item;
		@mkdir(implode("/",$step));
	}
}
function deletedir($dir)
{
	foreach (scandir($dir) as $file) {
		if ($file!="." && $file!="..") {
			if(is_dir($dir."/".$file)){
				deletedir($dir."/".$file);
			} elseif (is_file($dir."/".$file)) {
				unlink($dir."/".$file);
			}
    }
  }
  return @rmdir($dir);
}
function folderToZip($path, &$zipFile, $local)
{
	if ($zipFile) {
		foreach (scandir($path) as $file) {
			$fullpath = $path."/".$file;
			if (($file!="." && $file!="..") && is_dir($fullpath)) {
				folderToZip($fullpath, $zipFile, $local."/".$file);
			} elseif(is_file($fullpath)) {
				$zipFile->addFile($fullpath, $local."/".$file);
			}
		}
	}else return false;
}


/* OTHER ********************************************************************************************************************************************************/

function getProtocol()
{
	if (isset($_SERVER['HTTPS'])) {
		if (("on" == strtolower($_SERVER['HTTPS'])) || ("1" == $_SERVER['HTTPS'])) {
			$protocol = "https";
		}
	} elseif (
		isset($_SERVER['SERVER_PORT']) && ("443" == $_SERVER['SERVER_PORT'])
	) {
		$protocol = "https";
	} else {
		$protocol = "http";
	}
	return $protocol;
}

function translite($str = "")
{
	$dictionary = [
		"а"=>"a",	"б"=>"b",	"в"=>"v",	"г"=>"g",	"ґ"=>"g",	"д"=>"d",
		"е"=>"e",	"є"=>"ye",	"ж"=>"zh",	"з"=>"z",	"и"=>"i",	"і"=>"i",
		"ї"=>"yi",	"й"=>"y",	"к"=>"k",	"л"=>"l",	"м"=>"m",	"н"=>"n",
		"о"=>"o",	"п"=>"p",	"р"=>"r",	"с"=>"s",	"т"=>"t",	"у"=>"u",
		"ф"=>"f",	"х"=>"h",	"ы"=>"y",	"э"=>"e",	"ё"=>"e",	"ц"=>"ts",
		"ч"=>"ch",	"ш"=>"sh",	"щ"=>"shch","ю"=>"yu",	"я"=>"ya",	"ь"=>"",
		"ъ"=>"",	" "=>"-",	"ä"=>"a",	"ö"=>"o",	"ü"=>"u"
	];

	$str = mb_strtolower(trim($str), "UTF-8");
	if (preg_match("/і|ї|ґ|є/",$str)) {
		$dictionary['г'] = "h";
		$dictionary['и'] = "y";
		$dictionary['х'] = "kh";
	}
	$str = strtr($str, $dictionary);
	$str = preg_replace("/(?(?=\W)[^.-]|^$)/", "", $str);
	return preg_replace("/-{2,}/", "-", $str);
}

function round_time($ts, $step)
{
	return (floor(floor($ts / 60) / 60) * 3600 + floor(date("i", $ts) / $step) * $step * 60);
}
