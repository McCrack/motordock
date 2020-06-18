<?php

namespace App\Helpers\HTTP;

/*
 * Example:
HTTP::JSON([
  "Field_Name"=>"value"
])->setHeaders([
  "HEADER NAME"=>"value"
])->POST("https://example.caom");
*/
class HTTPRequest
{
    protected $options=[];
    protected $headers=[];
    protected $handle=null;
    protected $body;
    protected $encode;

    public function __construct()
    {
        $this->handle = curl_init();
        if ($this->handle) {
            $this->options = [
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_HEADER          => false,
                CURLOPT_ENCODING        => '',
                CURLOPT_NOPROGRESS      => true,
                CURLOPT_VERBOSE         => false,
                CURLOPT_SSL_VERIFYPEER  => false,
                CURLOPT_SSL_VERIFYHOST  => false
            ];
        } else {
            $this->exception();
        }
    }
    public function setOptions($options=[])
    {
      foreach ($options as $name=>$value) {
        $this->options[$name] = $value;
      }
      return $this;
    }
    public function setHeaders($headers=[])
    {
      foreach ($headers as $name=>$value) {
        $this->headers[$name] = $value;
      }
      return $this;
    }

    public static function RAW($body)
    {
      $http = new static();
      $http->body = $body;
      $http->encode = null;
      return $http;
    }
    public static function JSON($body=[])
    {
      $http = new static();
      $http->body = json_encode($body,JSON_UNESCAPED_UNICODE);
      $http->headers['Content-Type'] = "application/json";
      $http->encode = "JSON";
      return $http;
    }
    public static function XML($body)
    {
      $http = new static();
      switch (get_class($body)) {
          case "SimpleXMLElement":
            $http->body = $body->asXML();
            break;
          case "DOMNode":
          case "DOMElement":
            $body = $body->ownerDocument;
            // no break
          case "DOMDocument":
            $http->body = $body->saveXML();
            break;
          default:
            $http->body = $body;
            break;
      }
      $http->headers['Content-Type'] = "text/xml";
      $http->encode = "XML";
      return $http;
    }
    public static function NV($body=[])
    {
      $fields = [];
      foreach ($body as $key=>$val) {
          $fields[] = $key."=".$val;
      }
      $http = new static();
      $http->body = implode("&", $fields);
      $http->headers['Content-Type'] = "application/x-www-form-urlencoded";
      $http->encode = "NV";
      return $http;
    }

    /* Call Methods */
    public function GET($url)
    {
      if ($http->encode=="NV") {
        $url = parse_url($url);
        $endpoint =
          $url['scheme']
          ."://".
          $url['host']
          ."/".
          $url['path']
          ."?".
          $this->body.(empty($url['query']) ? "" : "&".$url['query']);
      }else {
        $endpoint = $url;
      }
      $this->setOptions([
        CURLOPT_POST=>false,
        CURLOPT_HTTPGET=>true,
        CURLOPT_URL=>$endpoint
      ]);
      return $this->call();
    }
    public function POST($url)
    {
      $this->setOptions([
        CURLOPT_HTTPGET=>false,
        CURLOPT_POST=>true,
        CURLOPT_URL=>$url,
        CURLOPT_POSTFIELDS=>$this->body
      ]);
      return $this->call();
    }

    public function call()
    {
      $headers = [];
      foreach ($this->headers as $name=>$value) {
          $headers[] = $name.": ".$value;
      }
      $this->options[CURLOPT_HTTPHEADER] = $headers;
      curl_setopt_array($this->handle, $this->options);
      return [
        "data"=>curl_exec($this->handle),
        "code"=>curl_getinfo($this->handle, CURLINFO_RESPONSE_CODE)
      ];
    }

    /* Additional options */
    public function setPort($port)
    {
      $this->options[CURLOPT_PORT] = $port;
      return $this;
    }
    public function setConnectTimeout($timeout)
    {
        $this->options[CURLOPT_CONNECTTIMEOUT] = $timeout;
    }
    public function setReadTimeout($timeout)
    {
        $this->options[CURLOPT_TIMEOUT] = $timeout;
    }
    public function setFollowRedirects($followRedirects=true, $max=0)
    {
        if($max) $this->options[CURLOPT_MAXREDIRS] = $max;
        $this->options[CURLOPT_FOLLOWLOCATION] = $followRedirects;
    }

    protected function exception()
    {
        throw new Exception(curl_error($this->handle), curl_errno($this->handle));
    }
    function __destruct()
    {
        curl_close($this->handle);
    }
}
