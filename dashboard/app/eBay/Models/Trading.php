<?php

namespace App\eBay\Models;

use App\eBay\Client;

class Trading extends BaseModel implements \IteratorAggregate
{
	public $count		= 0;
	public $total		= 0;
	public $current		= 0;
	public $entries		= 0;
	private $options	= [];
	private static $instance = null;

	/**
	 * Construct
	 * @param array $data
	 */
	private function __construct($data = [])
	{
		parent::__construct($data);
	}

	/**
	 * Set the market for next request
	 * 
	 * @param  int     $value
	 * @return Trading $instance
	 */
	public static function market($value)
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		self::$instance->market = $value;
		return self::$instance;
	}

	/**
	 * Set the listing start time for next request
	 * 
	 * @param  timestamp $value
	 * @return Trading   $instance
	 */
	public static function StartTimeFrom($value)
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance->where(
			'StartTimeFrom',
			is_numeric($value)
				? gmdate("Y-m-d\T00:00:00", $value)
				: $value
		);
	}

	/**
	 * Set the listing start time for next request
	 * 
	 * @param  timestamp $value
	 * @return Trading   $instance
	 */
	public static function StartTimeTo($value)
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance->where(
			'StartTimeTo',
			is_numeric($value)
				? gmdate("Y-m-d\T00:00:00", $value)
				: $value
		);
	}

	/**
	 * Set the Category ID for next request
	 * 
	 * @param  int     $value
	 * @return Trading $instance
	 */
	public static function CategoryID($value)
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance->where('CategoryID', $value);
	}

	/**
	 * Set parameter for next request
	 * 
	 * @param  string  $key
	 * @param  string  $value
	 * @return Trading $instance
	 */
	public static function where($key, $value)
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		self::$instance->options[$key] = $value;
		return self::$instance;
	}

	/**
	 * Get Seller List
	 * 
	 * @param. int $PageNumber
	 * @param. int $PerPage
	 * @return Trading $instance
	 */
	public function getItems($PageNumber = 1, $PerPage = 20)
	{
		$response = Client::trading("GetSellerList", $this->market)
			->post(function ($client) use ($PageNumber, $PerPage) {
				foreach ($this->options as $key => $val) {
					$client->query->addChild($key, $val);
				}
				$client->query->Pagination->EntriesPerPage = $PerPage;
				$client->query->Pagination->PageNumber = $PageNumber;
			});
		$this->data = [];
		if ($response['code'] == 200) {
			$xml = new \SimpleXMLElement($response['data']);
			if ((string) $xml->Ack == "Success") {
				foreach ($xml->ItemArray->Item as $item) {
					$json = json_encode($item);
					$this->data[] = new parent(
						json_decode($json, true)
					);
				}
				$this->current	= (int) $xml->PageNumber;
				$this->count 	= (int) $xml->ReturnedItemCountActual;
				$this->total	= (int) $xml->PaginationResult->TotalNumberOfPages;
				$this->entries	= (int) $xml->PaginationResult->TotalNumberOfEntries;
			}
		}
		return $this;
	}

	/**
	 * Get Item
	 *
	 * @param. int $item_id
	 * @return Trading $instance
	 */
	public static function getItem($item_id)
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		$response = Client::trading("GetItem", self::$instance->market)
			->post(function ($client) use ($item_id) {
				$client->query->ItemID = $item_id;
			});
		if ($response['code'] == 200) {
			$xml = new \SimpleXMLElement($response['data']);
			if ((string) $xml->Ack == "Success") {
				$json = json_encode($xml->Item[0]);
				self::$instance->data = json_decode($json, true);

				$this->current	=
					$this->count 	=
					$this->total	=
					$this->entries	= 1;
			}
		}
		return self::$instance;
	}

	/**
	 * Get Category Specifics
	 *
	 * @param. int $category_id
	 * @return Trading $instance
	 */
	public static function GetSpecifics($category_id, $market = null)
	{
		$response = Client::trading("GetCategorySpecifics", $market)
			->post(function ($client) use ($category_id) {
				$client->query->CategorySpecific->CategoryID = $category_id;
			});
		$items = [];
		if ($response['code'] == 200) {
			$xml = new \SimpleXMLElement($response['data']);
			if ((string) $xml->Ack == "Success") {
				foreach ($xml->Recommendations[0]->NameRecommendation as $item) {
					$name = (string) $item->Name;
					$items[$name] = [];
					foreach ($item->ValidationRules[0] as $rule => $value) {
						$items[$name][$rule] = (string) $value;
					}
					if ($item->ValidationRules->SelectionMode == "SelectionOnly") {
						$items[$name]['recommendations'] = [];
						foreach ($item->ValueRecommendation as $recommendation) {
							$items[$name]['recommendations'][] = (string) $recommendation->Value;
						}
					}
				}
			}
		}
		return $items;
	}
	/**
	 * Get eBay Details
	 */
	public function getDetails($detailName)
	{
		$response = Client::trading("GeteBayDetails", $this->market)
			->post(function ($client) use ($detailName) {
				$client->query->DetailName = $detailName;
			});
		$details = null;
		if ($response['code'] == 200) {
			$xml = new \SimpleXMLElement($response['data']);
			if ((string) $xml->Ack == "Success") {
				$json = json_encode($xml);
				return json_decode($json, true);
			}
		}
		return $details;
	}

	/**
	 * Add Fixed Price Item
	 */
	public static function create($item)
	{
		$response = Client::trading("AddFixedPriceItem", self::$instance->market)
			->post(function ($client) use ($item) {
				$client->makeQuery($client->query->Item, $item);
			});
		if ($response['code'] == 200) {
			$xml = new \SimpleXMLElement($response['data']);
			$json = json_encode($xml);
			return json_decode($json, true);
		}
	}
	/**
	 * End Fixed Price Item
	 */
	public static function EndItem($item)
	{
		$response = Client::trading("EndFixedPriceItem", null)
			->post(function ($client) use ($item) {
				$client->query->addChild('ItemID', $item);
			});
	}

	/**
	 * Iterator
	 */

	public function getIterator()
	{
		return new \ArrayIterator($this->data);
	}
}
