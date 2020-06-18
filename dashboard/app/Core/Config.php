<?php

namespace App\Core;

class Config
{
	private $data = null;
	private static $instance = null;
	private function __construct()
	{
		$cng = file_get_contents("../config.json");
		$this->data = json_decode($cng, true);
	}
	public static function getInstance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	public static function merge($path)
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		if (file_exists($path)) {
			$cng = file_get_contents($path);
			$cng = json_decode($cng, true);

			self::$instance->join($cng);
		}
		return self::$instance;
	}
	public static function join($cng = [])
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		self::$instance->data = array_merge(self::$instance->data, $cng);
		return self::$instance;
	}
	public function __get($key)
	{
		return $this->data[$key] ?? null;
	}
	public function __set($key, $val)
	{
		$this->data[$key] = $val;
	}
}
