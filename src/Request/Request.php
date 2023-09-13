<?php
namespace Juno\Request;

use Juno\Routing\Enums\Method as RouterMethod;
use Juno\Facades\Input;
use Juno\Facades\Get;
use Juno\Facades\Router;
use Arr;
use Str;

class Request extends InitRequest
{
  /**
   *   Returns header if present
   */
  protected function header(string $name = null) : array|string|null
  {
    if(empty($name))
      return $this->headers;

    return $this->headers[$name] ?? null;
  }

  /**
   *   Checks if header present
   */
  protected function hasHeader(string $name) : bool
  {
    return !empty($this->headers[$name]);
  }

  /**
   *   Checks if header present by name and with specific value
   */
  protected function headerWithValue(string $name, string $value, bool $case = false) : bool
  {
    if(!$this->hasHeader($name))
      return false;

    $header = $this->headers[$name];
    $value = trim($value);

    return $case ? $header == $value : strtolower($header) == strtolower($value);
  }

  /**
   *   Returns request method
   */
  public function method(bool $as_value = false) : RouterMethod|string
  {
    return $as_value ? $this->method->value : $this->method;
  }

  /**
   *   Returns request method
   */
  public function isMethod(string $name) : bool
  {
    return $this->method->value == strtolower(trim($name));
  }

  /**
   *   Returns IP
   */
  public function ip() : string
  {
    return $this->ip;
  }

  /**
   *   Returns uri
   */
  public function uri() : string|null
  {
    return $this->request_uri ?? null;
  }

  /**
   *   Returns uri path
   */
  public function path() : string|null
  {
    return $this->path ?? null;
  }

  /**
   *   Returns path segment(s)
   */
  public function segments(int $index = null) : string|array|null
  {
    if(empty($this->segments))
      return is_int($index) ? null : [];

    return is_int($index) ? ($this->segments[$index] ?? null) : $this->segments;
  }

  /**
   *   Returns host - example.com
   */
  public function host() : string|null
  {
    return $this->http_host ?? null;
  }

  /**
   *   Returns full host - http(s)://example.com
   */
  public function schemeHost() : string|null
  {
    return $this->scheme_host ?? null;
  }

  /**
   *   Returns protocol - http(s)
   */
  public function protocol() : string|null
  {
    return $this->protocol ?? null;
  }

  /**
   *   Returns search query string|array
   */
  public function query(bool $as_string = true, array $with = [], string|array $without = null) : array|string|null
  {
    //Apply with parameter
    if(!empty($with)){
      $with_parsed = [];
      foreach($with as $k => $v){
        if(is_numeric($k)){
          $with_parsed[$v] = '';
        }else{
          $with_parsed[$k] = $v;
        }
      }
      $query = !empty($with_parsed) ? array_merge($this->query, $with_parsed) : $this->query;
    }else{
      $query = $this->query;
    }

    //Apply without parameter
    if(!empty($query) && !empty($without)){
      if(is_string($without))
        $without = [$without];

      foreach($query as $k => $v){
        if(in_array($k, $without))
          unset($query[$k]);
      }
    }

    if(empty($query))
      return $as_string ? null : [];

    return $as_string ? http_build_query($query) : $query;
  }

  /**
   *   Returns url without query part
   */
  public function url() : string
  {
    return $this->domain . $this->path;
  }

  /**
   *   Returns full url
   */
  public function fullUrl() : string
  {
    return $this->domain . $this->request_uri;
  }

  /**
   *   Returns full url with appended additional query
   */
  public function fullUrlWithQuery(array $query) : string
  {
    if(empty($query))
      return $this->fullUrl();

    return $this->url() . '?' . $this->query(true, $query);
  }

  /**
   *   Returns full url with excluded arguments from query
   */
  public function fullUrlWithoutQuery(string|array $without) : string
  {
    return $this->url() . '?' . $this->query(true, [], $without);
  }

  /**
   *   Checks if POST or GET parameter present
   */
  public function has(string $name) : bool
  {
    return Arr::hasByDotPattern($this->all(), $name);
  }

  /**
   *   Checks if POST or GET any parameter from $list present and not empty
   */
  public function hasAny(array $list = []) : bool
  {
    $all = $this->all();
    if(empty($list)){
      foreach($all as $k => $v){
        if(!empty($v))
          return true;
      }
      return false;
    }

    foreach($list as $v){
      if(is_string($v) && $this->has($v))
        return true;
    }

    return false;
  }

  /**
   *   If request has input, call $onPass else call $onNotPass
   */
  public function whenHas(string $name, callable $onPass = null, callable $onNotPass = null) : void
  {
    if($this->has($name)){
      if(!empty($onPass))
        $onPass();
    }else{
      if(!empty($onNotPass))
        $onNotPass();
    }
  }

  /**
   *   Checks if POST or GET parameter exists
   */
  public function exists(string $name) : bool
  {
    return Arr::existsByDotPattern($this->all(), $name);
  }

  /**
   *   Checks if any of POST or GET parameter present
   */
  public function existsAny(array $list = []) : bool
  {
    $all = $this->all();
    if(empty($list))
      return !empty($all);

    foreach($list as $k => $v){
      if($this->exists($v))
        return true;
    }

    return false;
  }

  /**
   *   If request input exists call $onPass else call $onNotPass
   */
  public function whenExists(string $name, callable $onPass = null, callable $onNotPass = null) : void
  {
    if($this->exists($name)){
      if(!empty($onPass))
        $onPass();
    }else{
      if(!empty($onNotPass))
        $onNotPass();
    }
  }

  /**
   *   Returns data GET single item
   */
  public function get(string $name = null, $value_on_empty = null) : mixed
  {
    return empty($name) ? Get::all() : Get::get($name, $value_on_empty);
  }

  /**
   *   Returns data INPUT(POST|JSON) single item
   */
  public function input(string $name = null, $value_on_empty = null) : mixed
  {
    return empty($name) ? Input::all() : Input::get($name, $value_on_empty);
  }

  /**
   *   Returns data INPUT(POST|JSON) or GET single item
   */
  public function any(string $name, $value_on_empty = null) : mixed
  {
    return Arr::getByDotPattern($this->all(), $name, $value_on_empty);
  }

  /**
   *   Returns all data
   */
  public function all() : array
  {
    return array_merge(Get::all(), Input::all());
  }

  /**
   *   Returns data only by specific keys
   */
  public function only(string|array $only) : array
  {
    return Arr::getOnly($this->all(), $only);
  }

  /**
   *   Returns all data except specific keys
   */
  public function except(string|array $except) : array
  {
    return Arr::getExcept($this->all(), $except);
  }

  /**
   *   Checks if request wants a json response
   */
  public function expectsJson() : bool
  {
    return $this->headerWithValue('Accept', 'application/json');
  }

  /**
   *   Checks if path equal to
   */
  public function is(string $pattern) : bool
  {
    return Str::isEqualByStarPattern($this->path, $pattern);
  }

  /**
   *   Checks if current route name equal to
   */
  public function routeIs(string $name) : bool
  {
    $current_route = Router::currentRoute();
    return Str::isEqualByStarPattern($current_route->getName(), $name);
  }

  /**
   *   Returns accept types
   */
  public function getAcceptableContentTypes() : array
  {
    return $this->accept;
  }

  /**
   *   Returns true if any of types($list) in accept
   */
  public function accepts(string|array $list) : bool
  {
    if(empty($this->accept))
      return true;

    if(!is_array($list))
      $list = [$list];

    $accept_values = array_values($this->accept);
    foreach($list as $v){
      if(in_array(trim($v), $accept_values))
        return true;
    }

    return false;
  }

}