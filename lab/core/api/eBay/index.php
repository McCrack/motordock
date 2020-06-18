<?php

require_once "core/HTTPRequest.php";

class eBay
{
    public $query;
    public $headers=[];
    public $queryformat;
    private $config;
    private $endpoint;

    public function __construct()
    {
        $this->config = new config(__DIR__."/config.init");
    }
    public function post($callback)
    {
        $callback($this);
        return
        HTTP::{$this->queryformat}($this->query)
          ->setHeaders($this->headers)
          ->POST($this->endpoint);
    }
    public static function searching($cmd){
        $client = new eBay();
        $client->queryformat = "JSON";
        $client->headers = [
            "X-EBAY-SOA-SERVICE-NAME"=>"FindingService",
            "X-EBAY-SOA-SERVICE-VERSION"=>"1.13.0",
            "X-EBAY-SOA-GLOBAL-ID"=>$client->config->{"Default Market Name"},
            "X-EBAY-SOA-REQUEST-DATA-FORMAT"=>$client->queryformat,
            "X-EBAY-SOA-OPERATION-NAME"=>$cmd,
            "X-EBAY-SOA-SECURITY-APPNAME"=>$client->config->AppID
        ];

        $client->endpoint = "https://svcs.ebay.com/services/search/FindingService/v1";
        if (file_exists(__DIR__."/queries/searching/".$cmd.".json")) {
            $client->query = JSON::load(__DIR__."/queries/searching/".$cmd.".json");
        }
        return $client;
    }
    public static function trading($cmd){
        $client = new eBay();
        $client->queryformat = "XML";
        $client->headers = [
            "X-EBAY-API-SITEID"=>$client->config->{"Default Market ID"},
            "X-EBAY-API-COMPATIBILITY-LEVEL"=>"1113",
            "X-EBAY-API-REQUEST-ENCODING"=>$client->queryformat,
            "X-EBAY-API-CALL-NAME"=>$cmd,
            "X-EBAY-API-DEV-NAME"=>$client->config->DevID,
            "X-EBAY-API-APP-NAME"=>$client->config->AppID,
            "X-EBAY-API-CERT-NAME"=>$client->config->CertID
        ];
        $client->endpoint = "https://api.ebay.com/ws/api.dll";
        if (file_exists(__DIR__."/queries/trading/".$cmd.".xml")) {
            $client->query = simplexml_load_file(__DIR__."/queries/trading/".$cmd.".xml");
            $client->query->RequesterCredentials->eBayAuthToken = $client->config->{"Auth'n'Auth"};
        }

        return $client;
    }
    public static function shopping($cmd){
        $client = new eBay();
        $client->queryformat = "XML";
        $client->headers = [
            "X-EBAY-API-SITEID"=>$client->config->{"Default Market ID"},
            "X-EBAY-API-VERSION"=>1063,
            "X-EBAY-API-VERSIONHANDLING"=>"LatestEnumValues",
            "X-EBAY-API-REQUEST-ENCODING"=>$client->queryformat,
            "X-EBAY-API-RESPONSE-ENCODING"=>"XML",
            "X-EBAY-API-CALL-NAME"=>$cmd,
            "X-EBAY-API-APP-NAME"=>$client->config->AppID
        ];
        $client->endpoint = "http://open.api.ebay.com/shopping";
        if (file_exists(__DIR__."/queries/shopping/".$cmd.".xml")) {
            $client->query = simplexml_load_file(__DIR__."/queries/shopping/".$cmd.".xml");
        }

        return $client;
    }
}
