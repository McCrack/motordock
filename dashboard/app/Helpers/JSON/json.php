<?php

namespace App\Helpers\JSON;
 
class JSON {
    /**
     * @param STRING $path Path to file or php://input
     * 
     * @return Array
     */
	public static function load($path)
	{
		$json = file_get_contents($path);
		return json_decode($json, true);
	}
	/**
     * @param STRING $path Path to file
     * @param ARRAY $data
     * 
     * @return INT
     */
	public static function save($path, $data)
	{
		if(is_array($data) || is_object($data)){
			$data = self::encode($data);
			return file_put_contents($path, $data);
		}else {
			return null;
		}
	}

	public static function parse($str, $assoc = true)
	{
		return json_decode($str, $assoc);
	}
	public static function encode($array)
	{
		return json_encode($array, JSON_UNESCAPED_UNICODE);
	}


	public static function stringify($value){
		if(is_int($value)){
			return (string)$value;   
		}elseif(is_string($value)){
			$value=str_replace(array('\\', '/', '"', "\r", "\n", "\b", "\f", "\t"), array('\\\\', '\/', '\"', '\r', '\n', '\b', '\f', '\t'), $value);
			$convmap=array(0x80, 0xFFFF, 0, 0xFFFF);
			$result="";
			for($i=mb_strlen($value); $i--;){
				$mb_char = mb_substr($value, $i, 1);
				if(mb_ereg("&#(\\d+);", mb_encode_numericentity($mb_char, $convmap, "UTF-8"), $match)){
					$result = sprintf("\\u%04x", $match[1]) . $result;
				}else $result = $mb_char . $result;
			}
			return '"' . $result . '"';   
		}elseif(is_float($value)){ return str_replace(",", ".", $value);         
		}elseif(is_null($value)){ return 'null';
		}elseif(is_bool($value)){ return $value ? 'true' : 'false';
		}elseif(is_array($value)){
			$keys=array_keys($value);
			$with_keys=array_keys($keys)!==$keys;
		}elseif(is_object($value)){
			$with_keys=true;
		}else return '';
		$result=array();
		if($with_keys){
			foreach($value as $key=>$v){
				$result[]=self::stringify((string)$key).':'.self::stringify($v);    
			}
			return '{'.implode(',', $result).'}';     
		}else{
			foreach ($value as $key=>$val) {
				$result[]=self::stringify($val);    
			}
			return '['.implode(',', $result).']';
		}
	}
}