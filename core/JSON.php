<?php
class JSON
{
	public static function save($path, &$array)
  {
		if (is_string($array)) {
			$json = $array;
		} elseif (is_array($array) || is_object($array)) {
			$json = self::stringify($array);
		}
		return file_put_contents($path, $json);
	}
	public static function load($path, $assoc=true)
  {
		$str = file_get_contents($path);
		return json_decode($str, $assoc);
	}
	public static function parse(&$str, $assoc=true)
  {
    return json_decode($str, $assoc);
  }
	public static function encode(&$str)
  {
    return json_encode($str, JSON_UNESCAPED_UNICODE);
  }
	public static function stringify($value)
  {
		if (is_int($value)) {
			return (string)$value;
		} elseif (is_string($value)) {
			$value = str_replace(
        ['\\', '/', '"', "\r", "\n", "\b", "\f", "\t"],
        ['\\\\', '\/', '\"', '\r', '\n', '\b', '\f', '\t'],
        $value
      );
			$convmap = [0x80, 0xFFFF, 0, 0xFFFF];
			$result = "";
			for ($i=mb_strlen($value); $i--;) {
				$mb_char = mb_substr($value, $i, 1);
				if (mb_ereg(
            "&#(\\d+);",
            mb_encode_numericentity(
              $mb_char,
              $convmap,
              "UTF-8"
            ),
            $match
        )) {
					$result = sprintf("\\u%04x", $match[1]) . $result;
				} else {
          $result = $mb_char . $result;
        }
			}
			return '"'.$result.'"';
		} elseif (is_float($value)) {
      return str_replace(",", ".", $value);
		} elseif (is_null($value)) {
      return 'null';
		} elseif (is_bool($value)) {
      return $value ? 'true' : 'false';
		} elseif (is_array($value)) {
			$keys=array_keys($value);
			$with_keys=array_keys($keys)!==$keys;
		} elseif (is_object($value)) {
			$with_keys=true;
		} else {
      return '';
    }
		$result = [];
		if ($with_keys) {
			foreach ($value as $key=>$v) {
				$result[] = self::stringify((string)$key).':'.self::stringify($v);
			}
			return '{'.implode(',', $result).'}';
		} else {
			foreach ($value as $key=>$val) {
				$result[]=self::stringify($val);
			}
			return '['.implode(',', $result).']';
		}
	}
}
