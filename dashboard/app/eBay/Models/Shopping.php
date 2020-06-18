<?php

namespace App\eBay\Models;

use App\eBay\Client;

class Shopping extends BaseModel implements \IteratorAggregate
{
	public $count		= 0;
	public $total		= 0;
	public $current		= 0;

	private static $instance = null;

	private function __construct($data = [])
	{
		parent::__construct($data);
	}

	/** ****************** **/

	public static function market($value)
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		self::$instance->market = $value;
		return self::$instance;
	}
	/** ****************** **/
	/** Get Multiple Items **/
	public function getMultiple($IDs)
	{
		$response = Client::shopping("GetMultipleItems", self::$instance->market)
			->post(function ($client) use ($IDs) {
				foreach ($IDs as $item_id) {
					$client->query->addChild("ItemID", $item_id);
				}
			});
		$this->data = [];
		if ($response['code'] == 200) {
			$xml = new \SimpleXMLElement($response['data']);
			if ((string) $xml->Ack == "Success") {
				foreach ($xml->Item as $item) {
					$json = json_encode($item);
					$this->data[] = new self(json_decode($json, true));
				}
				$this->count	= count($this->data);
				$this->total	= 1;
				$this->current	= 1;
			}
		}
		return $this;
	}

	/** Get Single Item **/
	public static function getSingle($item_id)
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		$response = Client::shopping("GetSingleItem", self::$instance->market)
			->post(function ($client) use ($item_id) {
				$client->query->ItemID = $item_id;
			});

		if ($response['code'] == 200) {
			$xml = new \SimpleXMLElement($response['data']);
			if ((string) $xml->Ack == "Success") {
				$json = json_encode($xml->Item[0]);
				self::$instance->data = json_decode($json, true);

				$this->count	= 1;
				$this->total	= 1;
				$this->current	= 1;
			}
		}
		return self::$instance;
	}

	/** ****************** **/

	public function getIterator()
	{
		return new \ArrayIterator($this->data);
	}
}
