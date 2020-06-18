<?php

namespace App\eBay\Models;

class BaseModel
{
	protected $data = [];
	protected $market = null;

	protected function __construct($data = [])
    {
    	$this->data = $data;
    }

	public function __get($key)
    {
    	return $this->data[$key] ?? null;
    }
    public function __set($key, $value)
    {
    	$this->data[$key] = $value;
    }

    public function __isset($key)
    {
    	return isset($this->data[$key]);
    }
    public function __unset($key)
    {
    	unset($this->data[$key]);
    }
}