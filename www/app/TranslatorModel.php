<?php

namespace App;


class TranslatorModel
{
    private $dictionary = [];
	private static $instance = null;

	private function __construct($dictionary)
	{
		if (file_exists("../resources/lang/de/{$dictionary}.json")) {
			$json = file_get_contents("../resources/lang/".USER_LANG."/{$dictionary}.json");
        	$this->dictionary = json_decode($json, true);
		}
	}

	public static function getInstance($dictionary = "")
	{
		if (is_null(self::$instance)) {
			self::$instance = new self($dictionary);
		}
		return self::$instance;
	}
	public function __invoke($key, $substring = false)
	{
		if (isset($this->dictionary[strtolower($key)])) {
			return $this->dictionary[strtolower($key)] ?? $key;
		} elseif ($substring) {
			foreach ($this->dictionary as $subkey => $word) {
				$key = preg_replace("/\b({$subkey})\b/i", $word, $key);
			}
		}
		return $key;
	}
	public function __get($key){
		return $this->dictionary[strtolower($key)] ?? $key;
	}

	private function __clone() {}
	private function __wakeup() {}
}
