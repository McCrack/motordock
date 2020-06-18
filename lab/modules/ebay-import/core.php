<?php

require_once("core/HTTPRequest/index.php");

class EbayClient{
	protected $cng;
	protected $endpoint;
	protected $token = null;
	public $query,$response;
	public function __construct($path){
		$this->cng = JSON::load($path);
	}
	public function __get($name){
		return $this->response->{$name}[0];
	}
	public function setHeader($name, $value){
		$this->headers[$name] = $value;
	}
	protected function call($query){
		$headers = [];
		foreach($this->headers as $key=>$val){
			$headers[] = $key.": ".$val;
		}
		$s = curl_init();
		curl_setopt($s, CURLOPT_URL, $this->endpoint);
		curl_setopt($s, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($s, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($s, CURLOPT_POST, true);
		curl_setopt($s, CURLOPT_POSTFIELDS, $query);
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($s);

		curl_close($s);

		return $response;
	}
}
class searching extends EbayClient{
	protected $headers = [
		"X-EBAY-SOA-SERVICE-NAME"=>"FindingService",
		"X-EBAY-SOA-SERVICE-VERSION"=>"1.13.0",
		"X-EBAY-SOA-GLOBAL-ID"=>"EBAY-GB",
		"X-EBAY-SOA-REQUEST-DATA-FORMAT"=>"JSON",
		"Accept"=>"application/json",
		"Content-Type"=>"application/json"
	];
	public function __construct($path){
		parent::__construct($path);
		$this->headers['X-EBAY-SOA-SECURITY-APPNAME'] = $this->cng['AppID']['value'];
		$this->endpoint = "https://svcs.ebay.com/services/search/FindingService/v1";
	}
	protected function createQuery($name){
		$this->headers['X-EBAY-SOA-OPERATION-NAME'] = $name;

		if(file_exists("modules/ebay-import/queries/searching/".$name.".json")){
			//$query = new SimpleXMLElement("modules/ebay-import/queries/searching/".$name.".xml", null, true);
			$this->query = JSON::load("modules/ebay-import/queries/searching/".$name.".json");
		}else return false;
		return true;
	}

	/*~~~~%%%%%%%~~~~*/

	public function __call($name, $args){
		if($this->createQuery($name)){
			//$this->call($this->query->asXML());
			$response = $this->call(JSON::encode($this->query));
			$this->response = JSON::parse($response);
		}else return false;
		return true;
	}
	public function getVersion(){
		if($this->createQuery("getVersion")){
			//$this->call($this->query->asXML());
			$response = $this->call(JSON::encode($this->query));
			$this->response = JSON::parse($response);
		}else return false;
		
		$this->headers['X-EBAY-SOA-SERVICE-VERSION'] = $this->version;
		return $this->headers['X-EBAY-SOA-SERVICE-VERSION'];
	}
	public function findItemsIneBayStores(){
		if($this->createQuery("findItemsIneBayStores")){
			$response = $this->call(JSON::encode($this->query));
			$this->response = JSON::parse($response);
		}else return false;
		return true;

/*
		$http = HttpRequest::post($this->endpoint)->form([
			"param1" => "value",
			"param2" => "value",
			"file" => '@/home/vasya/attach.txt'
		])
		->header(HttpRequest::HEADER_USER_AGENT, 'Opera/9.60 (J2ME/MIDP; Opera Mini/4.2.14912/812; U; ru)')
		->header(HttpRequest::HEADER_REFERER, 'http://google.com');
*/
	}
}

class trading extends EbayClient{
	protected $headers = [
		"X-EBAY-API-SITEID"=>"3",
		"X-EBAY-API-COMPATIBILITY-LEVEL"=>"1113",
		"X-EBAY-API-REQUEST-ENCODING"=>"XML",
		"Content-Type"=>"text/xml;charset=utf-8"
	];
	public function __construct($path){
		parent::__construct($path);

		$this->headers['X-EBAY-API-DEV-NAME'] = $this->cng['DevID']['value'];
		$this->headers['X-EBAY-API-APP-NAME'] = $this->cng['AppID']['value'];
		$this->headers['X-EBAY-API-CERT-NAME'] = $this->cng['CertID']['value'];

		$this->endpoint = "https://api.ebay.com/ws/api.dll";
	}
	protected function createQuery($name){
		$this->headers['X-EBAY-API-CALL-NAME'] = $name;
		if(file_exists("modules/ebay-import/queries/trading/".$name.".xml")){
			$query = new SimpleXMLElement("modules/ebay-import/queries/trading/".$name.".xml", null, true);
			if(isset($query->RequesterCredentials->eBayAuthToken)){
				if(empty($this->token)){
					$this->token = file_get_contents("modules/ebay-import/token.txt");
				}
				$query->RequesterCredentials->eBayAuthToken = $this->token;
			}
			$this->query = $query;
		}else return false;
		return true;
	}
	
	/*~~~~%%%%%%%~~~~*/
	
	public function __call($name, $args){
		if($this->createQuery($name)){
			$response = $this->call($this->query->asXML());
			$this->response = new SimpleXMLElement($response);
		}else return false;
		return true;
	}
	public function GetCategories($id=131090,$level=2,$SiteID=3){
		if($this->createQuery("GetCategories")){
			$this->query->CategoryParent = $id;
			$this->query->LevelLimit = $level;
			$this->query->CategorySiteID = $SiteID;
			$response = $this->call($this->query->asXML());
			$this->response = new SimpleXMLElement($response);
		}else return false;
		return true;
	}
	public function GetItem($ItemID){
		if($this->createQuery("GetItem")){
			$this->query->ItemID = $ItemID;
			$response = $this->call($this->query->asXML());
			$this->response = new SimpleXMLElement($response);
		}else return false;
		return true;
	}
	public function GetSessionID($ItemID){
		if($this->createQuery("GetSessionID")){
			$this->query->RuName = $this->cng['RuName']['value'];
			$response = $this->call($this->query->asXML());
			$this->response = new SimpleXMLElement($response);
		}else return false;
		return true;
	}
	public function GetSellerList(){
		if($this->createQuery("GetSellerList")){
			/*...*/
			$response = $this->call($this->query->asXML());
			$this->response = new SimpleXMLElement($response);
		}else return false;
		return true;
	}
}
class shopping extends EbayClient{
	protected $headers = [
		"X-EBAY-API-SITEID"=>"3",
		"X-EBAY-API-VERSION"=>"1063",
		"X-EBAY-API-VERSIONHANDLING"=>"LatestEnumValues",
		"X-EBAY-API-REQUEST-ENCODING"=>"XML",
		"Content-Type"=>"text/xml"
	];
	public function __construct($path){
		parent::__construct($path);

		$this->headers['X-EBAY-API-APP-NAME'] = $this->cng['AppID']['value'];

		$this->endpoint = "http://open.api.ebay.com/shopping";
	}
	protected function createQuery($name){
		$this->headers['X-EBAY-API-CALL-NAME'] = $name;
		if(file_exists("modules/ebay-import/queries/shopping/".$name.".xml")){
			$query = new SimpleXMLElement("modules/ebay-import/queries/shopping/".$name.".xml", null, true);
			if(isset($query->RequesterCredentials->eBayAuthToken)){
				if(empty($this->token)){
					$this->token = file_get_contents("modules/ebay-import/token.txt");
				}
				$query->RequesterCredentials->eBayAuthToken = $this->token;
			}
			$this->query = $query;
		}else return false;
		return true;
	}
	
	/*~~~~%%%%%%%~~~~*/
	
	public function __call($name, $args){
		if($this->createQuery($name)){
			$response = $this->call($this->query->asXML());
			$this->response = new SimpleXMLElement($response);
		}else return false;
		return true;
	}
}
class browse{
	private $s;
	protected $endpoint;
	protected $token = null;
	public $query,$response;
	protected $headers = [
		"Content-Type: application/json"
	];
	public function __construct(){
		$this->headers[] = "Authorization: Bearer ".file_get_contents("modules/ebay-import/oAuth.txt");
		$this->endpoint = "https://api.ebay.com/buy/browse/v1";

		$this->s = curl_init();
	}
	protected function call($endpoint){
		
		curl_setopt($this->s, CURLOPT_URL, $endpoint);
		curl_setopt($this->s, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($this->s, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($this->s, CURLOPT_HTTPHEADER, $this->headers);
		curl_setopt($this->s, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($this->s);
		curl_close($this->s);
		$this->response = JSON::parse($response);
	}

	public function __call($name, $args){
		$this->call($this->endpoint."/".$name."/".$args[0]."/");
		return true;
	}
	public function search($query){
		$this->call($this->endpoint."/item_summary/search?q=".$query);
	}
	public function item($id){
		$this->call($this->endpoint."/item/".$id."/");
	}
}
class Translate{
	public static function __callStatic($name, $query){
		$headers = [
			"Content-Type: application/json",
			"Authorization: Bearer ".file_get_contents("modules/ebay-import/oAuth.txt")
		];
		$query[0]['translationContext'] = $name;

		$s = curl_init();
		curl_setopt($s, CURLOPT_URL, "https://apid.ebay.com/commerce/translation/v1/translate");
		curl_setopt($s, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($s, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($s, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($s, CURLOPT_POST, true);
		curl_setopt($s, CURLOPT_POSTFIELDS, JSON::encode($query[0]));
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($s);
		curl_close($s);
		return JSON::parse($response);
	}
}

?>