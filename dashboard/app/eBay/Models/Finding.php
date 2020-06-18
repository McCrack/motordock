<?php

namespace App\eBay\Models;

use App\eBay\Client;

class Finding extends BaseModel implements \IteratorAggregate
{
    private static $instance = null;
    
    private $options	= [];
    private $filters	= [];

    public $count		= 0;
    public $total		= 0;
    public $current		= 0;
    public $entries		= 0;

    private function __construct($data = [])
    {
    	parent::__construct($data);
    }

    /** ******************* **/

    public static function market($value)
    {
    	if (is_null(self::$instance)) {
    		self::$instance = new self();
    	}
    	self::$instance->market = $value;
    	return self::$instance;
    }
    public static function keywords($value)
    {
    	if (is_null(self::$instance)) {
    		self::$instance = new self();
    	}
    	return self::$instance->where('keywords', $value);
    }
    public static function category($value)
    {
    	if (is_null(self::$instance)) {
    		self::$instance = new self();
    	}
    	return self::$instance->where('categoryId', $value);
    }
    public static function where($key, $value)
    {
    	if (is_null(self::$instance)) {
    		self::$instance = new self();
    	}
    	self::$instance->options[$key] = $value;
    	return self::$instance;
    }
    public static function store($value)
    {
    	if (is_null(self::$instance)) {
    		self::$instance = new self();
    	}
    	return self::$instance->filterset('Seller', $value);
    }
    public static function StartTimeFrom($value)
    {
    	if (is_null(self::$instance)) {
    		self::$instance = new self();
    	}
    	return self::$instance->filterset('StartTimeFrom', is_numeric($value)
    		? gmdate("Y-m-d\TH:i:s", $value)
    		: $value
    	);
    }
    public static function filterset($name, $value)
    {
    	if (is_null(self::$instance)) {
    		self::$instance = new self();
    	}
    	self::$instance->filters[] = [
    		'name'	=> $name,
    		'value'	=> $value
    	];
    	return self::$instance;
	}
	/** ******************* **/
	/** Find Items Advanced **/
	public function advanced($pageNum = 1, $perPage = 20){
		$response = Client::searching('findItemsAdvanced', $this->market)
			->post(function($client) use ($perPage, $pageNum){
				foreach ($this->options as $key => $val) {
					$client->query['findItemsAdvancedRequest'][$key] = $val;
				}
				if (count($this->filters) > 0) {
					$client->query['findItemsAdvancedRequest']['itemFilter'] = $this->filters;
				}
				$client->query['findItemsAdvancedRequest']['paginationInput']['entriesPerPage'] = $perPage;
				$client->query['findItemsAdvancedRequest']['paginationInput']['pageNumber'] = $pageNum;
            });
        $this->prepare($response);
        return $this;
	}

	/** Find Completed Items **/
	public static function Completed($pageNum = 1, $perPage = 100)
    {
        $response = self::searching('findCompletedItems')
            ->post(function($client) use ($pageNum, $perPage){
                if (count($this->filters) > 0) {
					$client->query['findItemsAdvancedRequest']['itemFilter'] = $this->filters;
				}

                $client->query['findItemsAdvancedRequest']['paginationInput']['entriesPerPage'] = $perPage;
				$client->query['findItemsAdvancedRequest']['paginationInput']['pageNumber'] = $pageNum;
            });
        $this->prepare($response);
        return $this;
    }

    /** Find Items In eBay Stores **/
	public static function inStores($seller, $pageNum = 1, $perPage = 100)
	{
		$response = Client::searching('findItemsIneBayStores')
			->post(function($client) use ($seller, $pageNum, $perPage){
				
				$client->query['findItemsIneBayStoresRequest']['storeName'][] = $seller;
				
				if (count($this->filters) > 0) {
					$client->query['findItemsAdvancedRequest']['itemFilter'] = $this->filters;
				}
				$client->query['findItemsAdvancedRequest']['paginationInput']['entriesPerPage'] = $perPage;
				$client->query['findItemsAdvancedRequest']['paginationInput']['pageNumber'] = $pageNum;
			});
		$this->prepare($response);
        return $this;
    }

    /** ******************* **/

    private function prepare($response)
    {
    	
    	$prepare = function($prepare, $data){
    		foreach ($data as &$field) {
    			if (is_array($field)) {
    				if (is_array($field[0])) {
    					$field = $prepare($prepare, $field[0]);
    				} else {
    					$field = $field[0];
    				}
    			}
    		}
    		return $data;
    	};

    	$this->data = [];
    	if ($response['code'] == 200) {
            $response = json_decode($response['data'], true)['findItemsAdvancedResponse'][0];
            if ($response['ack'][0] == "Success") {
				if ($response['paginationOutput'][0]['totalPages'][0] > 0) {
    				foreach ($response['searchResult'][0]['item'] as $item) {
						$this->data[] = new parent($prepare($prepare, $item));
					}
					$this->count	= count($this->data);
					$this->total	= $response['paginationOutput'][0]['totalPages'][0];
    				$this->current	= $response['paginationOutput'][0]['pageNumber'][0];
    				$this->entries	= $response['paginationOutput'][0]['totalEntries'][0];
				}
			}
        }
    }

    /** ******************* **/

    public function getIterator() {
    	return new \ArrayIterator($this->data);
  	}
}
