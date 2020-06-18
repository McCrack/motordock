<?php

namespace App\eBay;

use Illuminate\Http\Request;

class Client
{
    public $query;
    public $headers = [];
    public $queryformat;
    private $config;
    private $endpoint;

    public function __construct()
    {
        $this->config = \JSON::load(__DIR__ . '/config.json');
    }

    public function post($callback)
    {
        $callback($this);
        return \HTTPRequest::{$this->queryformat}($this->query)
            ->setHeaders($this->headers)
            ->POST($this->endpoint);
    }
    public static function searching($cmd, $market = null)
    {
        $client = new Client();
        $client->queryformat = "JSON";
        $client->headers = [
            "X-EBAY-SOA-SERVICE-NAME" => "FindingService",
            "X-EBAY-SOA-SERVICE-VERSION" => "1.13.0",
            "X-EBAY-SOA-REQUEST-DATA-FORMAT" => $client->queryformat,
            "X-EBAY-SOA-OPERATION-NAME" => $cmd,
            "X-EBAY-SOA-SECURITY-APPNAME" => $client->config['AppID']
        ];

        $client->headers['X-EBAY-SOA-GLOBAL-ID'] = empty($market)
            ? $client->config['markets'][$client->config['market']]
            : $market;

        $client->endpoint = "https://svcs.ebay.com/services/search/FindingService/v1";

        if (file_exists(__DIR__ . "/queries/searching/{$cmd}.json")) {
            $client->query = \JSON::load(__DIR__ . "/queries/searching/{$cmd}.json");
        }

        return $client;
    }
    public static function trading($cmd, $market = null)
    {
        $client = new Client();
        $client->queryformat = "XML";
        $client->headers = [
            "X-EBAY-API-COMPATIBILITY-LEVEL" => "1113",
            "X-EBAY-API-REQUEST-ENCODING" => $client->queryformat,
            "X-EBAY-API-CALL-NAME" => $cmd,
            "X-EBAY-API-DEV-NAME" => $client->config['DevID'],
            "X-EBAY-API-APP-NAME" => $client->config['AppID'],
            "X-EBAY-API-CERT-NAME" => $client->config['CertID']
        ];

        $client->headers['X-EBAY-API-SITEID'] = $market ?? $client->config['market'];

        $client->endpoint = "https://api.ebay.com/ws/api.dll";
        if (file_exists(__DIR__ . "/queries/trading/" . $cmd . ".xml")) {
            $client->query = simplexml_load_file(__DIR__ . "/queries/trading/" . $cmd . ".xml");
            $client->query->RequesterCredentials->eBayAuthToken = \Auth::user()->token;
            //$client->query->RequesterCredentials->eBayAuthToken = $client->config['token'];
        }

        return $client;
    }
    public static function shopping($cmd, $market = null)
    {
        $client = new Client();
        $client->queryformat = "XML";
        $client->headers = [
            "X-EBAY-API-VERSION" => 1063,
            "X-EBAY-API-VERSIONHANDLING" => "LatestEnumValues",
            "X-EBAY-API-REQUEST-ENCODING" => $client->queryformat,
            "X-EBAY-API-RESPONSE-ENCODING" => "XML",
            "X-EBAY-API-CALL-NAME" => $cmd,
            "X-EBAY-API-APP-NAME" => $client->config['AppID']
        ];

        $client->headers['X-EBAY-API-SITEID'] = empty($market)
            ? $client->config['market']
            : $market;

        $client->endpoint = "http://open.api.ebay.com/shopping";
        if (file_exists(__DIR__ . "/queries/shopping/" . $cmd . ".xml")) {
            $client->query = simplexml_load_file(__DIR__ . "/queries/shopping/" . $cmd . ".xml");
        }

        return $client;
    }

    /********************/

    function makeQuery($query, $data)
    {
        $parser = function ($parser, $query, $data) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    if (array_keys($value) !== range(0, count($value) - 1)) {
                        $field = $query->addChild($key);
                        $parser($parser, $field, $value);
                    } else {
                        foreach ($value as $val) {
                            if (is_array($val)) {
                                $field = $query->addChild($key);
                                $parser($parser, $field, $val);
                            } else {
                                $query->addChild($key, $val);
                            }
                        }
                    }
                } else {
                    $query->addChild($key, htmlspecialchars($value));
                }
            }
        };
        $parser($parser, $query, $data);
    }

    /********************/

    public static function findItemsAdvanced(
        $keywords,
        $categoryId,
        $filters,
        $market = null,
        $pagination = ['offset' => 1, 'perPage' => 100]
    ) {
        $response = self::searching('findItemsAdvanced', $market)
            ->post(function ($client) use ($keywords, $categoryId, $filters, $pagination) {
                $client->query['findItemsAdvancedRequest']['keywords'] = $keywords;
                $client->query['findItemsAdvancedRequest']['categoryId'] = $categoryId;
                if (count($filters) > 0) {
                    $client->query['findItemsAdvancedRequest']['itemFilter'] = $filters;
                }

                $client->query['findItemsAdvancedRequest']['paginationInput']['entriesPerPage'] = $pagination['perPage'];
                $client->query['findItemsAdvancedRequest']['paginationInput']['pageNumber'] = $pagination['offset'];
            });
        return self::findingResultDecode($response, 'findItemsAdvancedResponse');
    }
    public static function findItemsIneBayStores($timeoffset, $seller, $pageoffset = 1)
    {
        $response = self::searching('findItemsIneBayStores')
            ->post(function ($client) use ($timeoffset, $seller, $pageoffset) {
                $client->query['findItemsIneBayStoresRequest']['itemFilter'][] = [
                    'name'  => "StartTimeFrom",
                    'value' => gmdate("Y-m-d\TH:i:s", $timeoffset)
                ];

                $client->query['findItemsIneBayStoresRequest']['storeName'][] = $seller;
                $client->query['findItemsIneBayStoresRequest']['paginationInput']['entriesPerPage'] = 100;
                $client->query['findItemsIneBayStoresRequest']['paginationInput']['pageNumber'] = $pageoffset;
            });
        if ($response['code'] == 200) {
            $response = \JSON::parse($response['data']);
            $response = $response['findItemsIneBayStoresResponse'][0];
            if ($response['ack'][0] == "Success") {
                if ($response['paginationOutput'][0]['totalPages'][0] > 0) {
                    return [
                        'ack'        => "Success",
                        'pagination' => $response['paginationOutput'][0],
                        'items'      => $response['searchResult'][0]['item']
                    ];
                }
            }
        }
        return [
            'ack'        => "Fail",
            'items'      => []
        ];
    }
    public static function findCompletedItems($timeoffset, $sellers, $pageoffset = 1)
    {
        $response = self::searching('findCompletedItems')
            ->post(function ($client) use ($timeoffset, $sellers) {
                $sellersName = [];
                foreach ($sellers as $seller) {
                    $sellersName[] = $seller->SellerName;
                }
                $client->query['findCompletedItemsRequest']['itemFilter'][] = [
                    "name"  => "Seller",
                    "value" => $sellersName
                ];
                $client->query['findCompletedItemsRequest']['itemFilter'][] = [
                    'name'  => "StartTimeFrom",
                    'value' => gmdate("Y-m-d\TH:i:s", $timeoffset)
                ];

                $client->query['findCompletedItemsRequest']['paginationInput']['entriesPerPage'] = 100;
            });
        if ($response['code'] == 200) {
            $response = \JSON::parse($response['data']);
            $response = $response['findCompletedItemsResponse'][0];
            if ($response['ack'][0] == "Success") {
                if ($response['paginationOutput'][0]['totalPages'][0] > 0) {
                    return [
                        'ack'        => "Success",
                        'pagination' => $response['paginationOutput'][0],
                        'items'      => $response['searchResult'][0]['item']
                    ];
                }
            }
        }
        return [
            'ack'        => "Fail",
            'items'      => []
        ];
    }
    /** Get Extended Data **/
    public static function GetMultipleItems($items)
    {
        $response = self::shopping("GetMultipleItems")
            ->post(function ($client) use ($items) {
                foreach ($items as $item) {
                    $client->query->addChild("ItemID", $item->eBay_id);
                }
            });

        if ($response['code'] == 200) {
            $xml = new \SimpleXMLElement($response['data']);
            return $xml;
        } else {
            return null;
        }
    }
    public static function GetSingleItem($eBayID)
    {
        $response = self::shopping("GetSingleItem")
            ->post(function ($client) use ($eBayID) {
                $client->query->addChild("ItemID", $eBayID);
            });

        if ($response['code'] == 200) {
            $xml = new \SimpleXMLElement($response['data']);
            return $xml;
        } else {
            return null;
        }
    }

    /*************************************/

    public static function findingResultDecode(&$response, $root)
    {
        if ($response['code'] == 200) {
            $response = \JSON::parse($response['data']);
            $response = $response[$root][0];
            if ($response['ack'][0] == "Success") {
                if ($response['paginationOutput'][0]['totalPages'][0] > 0) {
                    return [
                        'ack'        => "Success",
                        'pagination' => $response['paginationOutput'][0],
                        'items'      => $response['searchResult'][0]['item']
                    ];
                }
            }
        }
        return [
            'ack'        => "Fail",
            'items'      => []
        ];
    }

    /*************************************/

    public function BulkData($callback)
    {
        $dom = new \DOMDocument();
        $dom->load(__DIR__ . "/queries/trading/BulkDataExchange.xml");

        $callback($this, $dom);
        /*
        foreach ($items as $item) {
            $query = clone $client->query;
            $client->makeQuery($query->Item, $item);
            $node = dom_import_simplexml($query);
            $node = $dom->importNode($node, true);
            $dom->documentElement->appendChild($node);
        }
        $client->query = simplexml_import_dom($dom);
        */

        return $this;
    }
}
