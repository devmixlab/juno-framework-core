<?php
namespace Juno\Request;

use Juno\Routing\Enums\Method as RouterMethod;
use Juno\Facades\Input;
use Juno\Facades\Get;

class InitRequest{

  protected RouterMethod $method;

  protected string $ip;
  protected string $request_uri;
  protected string $request_method;
  protected string $http_host;
  protected string $protocol;
  protected string $path;
  protected array $segments = [];
  protected array $query = [];
  protected string $scheme_host;
  protected string $full_url;
  protected array $headers = [];

  protected array $accept = [];

  public function __construct()
  {
    $this->setHeaders()
      ->setUrlData()
      ->setMethod()
      ->setAcceptableContentTypes();
  }

  protected function setAcceptableContentTypes() : self
  {
    if(!$this->hasHeader('accept'))
      return $this;

    $accept = $this->header('accept');
    $accept = explode(',', $accept);
    $accept = array_map(function($itm) {
      $arr = explode(';', $itm);
      return $arr[0];
    }, $accept);

    $this->accept = $accept;

    return $this;
  }

  protected function setHeaders() : self
  {
    foreach(getallheaders() as $k => $v)
      $this->headers[$this->normalizeHeaderName($k)] = trim($v);

    return $this;
  }

  protected function setUrlData() : self
  {
    $this->ip = $_SERVER['REMOTE_ADDR'];
    $this->request_uri = $_SERVER['REQUEST_URI'];
    $this->request_method = strtolower($_SERVER['REQUEST_METHOD']);
    $this->http_host = $_SERVER['HTTP_HOST'];
//    dd($this->http_host);
    $this->protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";

    $request_uri_arr = parse_url($this->request_uri);

    $this->path ??= $request_uri_arr['path'];
    if(!empty($this->path)){
      $segments = explode('/', $this->path);
      $segments = array_values(array_filter(array_map(fn($itm) => trim($itm), $segments), fn($itm) => !empty($itm)));
      $this->segments = $segments;
    }

    if(!empty($request_uri_arr['query'])){
      $query = $request_uri_arr['query'];
      parse_str($query, $this->query);
    }

    $this->scheme_host = $this->protocol . '://' . $this->http_host;
    $this->full_url = $this->domain . '/' . $this->request_uri;

    return $this;
  }

  protected function setMethod() : self
  {
    if($this->request_method == 'post' && Input::has('__method')){
      $method = Input::get('__method');
      $method = strtolower(trim($method));
    }else{
      $method = $this->request_method;
    }

    $this->method = RouterMethod::tryFrom($method);

    return $this;
  }

  protected function normalizeHeaderName(string $name) : string
  {
    return strtolower(trim($name));
  }

  public function __get(string $name)
  {
    if(Input::has($name))
      return Input::get($name);

    if(Get::has($name))
      return Get::get($name);

    return null;
  }

  public function __call(string $method, array $args)
  {
    if(in_array($method, ['header','hasHeader','headerWithValue'])){
      if(!empty($args[0]))
        $args[0] = $this->normalizeHeaderName($args[0]);

      return call_user_func_array([$this, $method], $args);
    }
  }

}