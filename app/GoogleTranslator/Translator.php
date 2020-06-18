<?php

namespace GoogleTranslator;

use HTTPRequest;
use GoogleTranslate\TokenGenerator;

class Translator
{
  public $UserAgent = "";//"AndroidTranslate/5.3.0.RC02.130475354-53000263 5.1 phone";

  private $url = "https://translate.google.com/translate_a/single";
  private $endpoint;
  private $options = [
    "client"=>"t",
    "hl"=>"en",
    "dt"=>"t",
    "sl"=>null,
    "tl"=>null,
    "ie"=>"UTF-8",
    "oe"=>"UTF-8",
    "multires"=>1,
    "otf"=>0,
    "pc"=>1,
    "trs"=>1,
    "ssel"=>0,
    "tsel"=>0,
    "kc"=>1,
    "tk"=>null
  ];
  private $data;

  public function __construct($direction){
    $dir = each($direction);
    $this->options['sl'] = $dir['key'];
    $this->options['tl'] = $dir['value'];
  }
  public static function direction($direction){
    return new static($direction);
  }
  public function translate($data)
  {
    $this->data = urlencode($data);
    if (strlen($this->data) >= 5000) {
      throw new Exception('Maximum number of characters exceeded: 5000');
    }
    // Generate Token
    $this->generateToken($data);
    // Build endpoint URL
    $this->buildEndpoint();

    return $this->call(3);
  }
  private function generateToken($data)
  {
    $tokenProvider = new TokenGenerator();
    $this->options['tk'] = $tokenProvider->generateToken(
      $this->options['sl'],
      $this->options['tl'],
      $data
    );
  }
  private function buildEndpoint()
  {
    $query = [];
    foreach($this->options as $name=>$value) {
      $query[] = $name."=".$value;
    }
    $this->endpoint = $this->url."?".implode("&", $query);
  }
  public function call($shots)
  {
    $response = HTTPRequest::NV([
      "q"=>$this->data,
    ])->setOptions([
      CURLOPT_CONNECTTIMEOUT => 15,
      CURLOPT_ENCODING => "UTF-8",
      CURLOPT_USERAGENT => $this->$UserAgent
    ])->POST($this->endpoint);

    if ($response['data']===false || $response['code']!=200) {
      if ((--$shots) > 0) {
        usleep(1500000);    // Timeout 1.5 sec
        return $this->call($shots);
      } else {
        return urldecode($this->data);
      }
    } else {
      $regexes = ["/,+/"=>",", "/\[,/"=>"["];
      $body = preg_replace(array_keys($regexes), array_values($regexes), $response['data']);
      $body = json_decode($body, true);
      return $body[0][0][0];
    }
  }
}
