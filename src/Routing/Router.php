<?php
namespace Juno\Routing;

use Juno\Routing\Enums\Method;
use Juno\Routing\Enums\PartsTypes;
use Juno\Exceptions\UrlGenerationException;
use Juno\Facades\Request;

class Router extends MainRouter{

  public function getCurrentRouteAction() : string|null
  {
    if(empty($this->current_route))
      return null;

    $action = $this->current_route->getAction();
    return is_callable($action) ? null : $action;
  }

  public function getCurrentRouteController() : string|null
  {
    return empty($this->current_route) ? null : $this->current_route->getController();
  }

  public function getCurrentRouteName() : string|null
  {
    return empty($this->current_route) ? null : $this->current_route->getName();
  }

  public function setCurrentRoute(Route $route) : void
  {
    $this->current_route = $route;
  }

  public function currentRoute() : Route|null
  {
    return $this->current_route ?? null;
  }

  public function isCurrentRoute(string $route_name) : bool
  {
    $route = $this->getRouteByName($route_name);
    if(empty($route))
      return false;

    $method = Request::getMethod();
    $request_uri = Request::getUri();
    return $route->isMatch($request_uri, $method);
  }

  public function setRouteNameIndexAliasess(Route $route) : void
  {
    $idx = array_search($route, $this->routes);
    $route_name = $route->getName();
    $this->route_name_index_aliasess[$route_name] = $idx;
  }

  public function getUrlByRouteName(string $route_name, array $params = [], bool $absolute = false) : string|null
  {
    $route = $this->getRouteByName($route_name);

    if(empty($route))
      return null;

    $uri = "/";

    $parts = $route->getParts();
    if(!empty($parts)){
      $uri_arr = [];

      foreach($parts as $k => $part){
        $part_name = $part->getName();
        if($part->isParam()){
          if($part->isParamRequired() && !isset($params[$part_name]))
            throw UrlGenerationException::forMissingParameters($route, [$part_name]);

          if(isset($params[$part_name])){
            $part_value = $params[$part_name];
            if($part->isParamRegex() && !preg_match($part->getFullParamRegex(), $part_value))
              throw UrlGenerationException::forWrongParameters($route, [$part_name]);

            $uri_arr[] = $part_value;
            unset($params[$part_name]);
          }
        }else{
          $uri_arr[] = $part_name;
        }
      }

      $uri .= implode("/", $uri_arr);
    }

    if(!empty($params)){
      $http_build_query = http_build_query($params);
      if(!empty($http_build_query))
        $uri .= '?' . $http_build_query;
    }

    if($absolute === true)
      $uri = Request::schemeHost() . $uri;

    return $uri;
  }

  public function resource(string $name, string $controller, array $params = []) : self
  {
    ['only' => $only, 'except' => $except] = array_merge(['only' => null, 'except' => null], $params);

    $is_make_route = function($name) use ($only, $except) {
      if(!empty($only))
        return is_array($only) ? in_array($name, $only) : false;
      if(!empty($except))
        return is_array($except) ? !in_array($name, $except) : true;
      return true;
    };

    $this->group(function() use ($name, $controller, $is_make_route) {
      $this->as($name . '.')
        ->prefix('/' . $name)
        ->group(function() use ($controller, $is_make_route) {

          if($is_make_route('index'))
            $this->get("/",[$controller, 'index'])
              ->name('index');

          if($is_make_route('create'))
            $this->get("/create",[$controller, 'create'])
              ->name('create');

          if($is_make_route('store'))
            $this->post("/",[$controller, 'store'])
              ->name('store');

          if($is_make_route('show'))
            $this->get("/{id}",[$controller, 'show'])
              ->name('show');

          if($is_make_route('edit'))
            $this->get("/{id}/edit",[$controller, 'edit'])
              ->where('id', '\d+')
              ->name('edit');

          if($is_make_route('update'))
            $this->match(['put','patch'], "/{id}",[$controller, 'update'])
              ->name('update');

          if($is_make_route('destroy'))
            $this->delete("/{id}",[$controller, 'destroy'])
              ->name('destroy');

        });
    });

    return $this;
  }

  public function getMatchRoute(string $uri, Method $method) : Route|null
  {
    if(empty($this->routes))
      return null;

    foreach($this->routes as $route)
      if($route->isMatch($uri, $method))
        return $route->setUriParamsValues();

    return null;
  }

  public function group(array|callable $params, callable $func = null) : self
  {
    if(is_callable($params)){
      $this->proccessGroupCallback($params);
      return $this;
    }else if(is_array($params) && empty($func)){
      return $this;
    }

    $this->setTemporaryGroupParams($params);

    $this->proccessGroupCallback($func);

    return $this;
  }

  public function get($uri, $handler) : Route
  {
    return $this->add(uri: $uri, handler: $handler, methods: Method::GET);
  }

  public function head($uri, $handler) : Route
  {
    return $this->add(uri: $uri, handler: $handler, methods: Method::HEAD);
  }

  public function post($uri, $handler) : Route
  {
    return $this->add(uri: $uri, handler: $handler, methods: Method::POST);
  }

  public function put($uri, $handler) : Route
  {
    return $this->add(uri: $uri, handler: $handler, methods: Method::PUT);
  }

  public function patch($uri, $handler) : Route
  {
    return $this->add(uri: $uri, handler: $handler, methods: Method::PATCH);
  }

  public function delete($uri, $handler) : Route
  {
    return $this->add(uri: $uri, handler: $handler, methods: Method::DELETE);
  }

  public function options($uri, $handler) : Route
  {
    return $this->add(uri: $uri, handler: $handler, methods: Method::OPTIONS);
  }

  public function match(array $methods, $uri, $handler) : Route
  {
    return $this->add(uri: $uri, handler: $handler, methods: $methods);
  }

  public function any($uri, $handler) : Route
  {
    return $this->add(uri: $uri, handler: $handler);
  }

  public function controller(string $value) : self
  {
    $this->setTemporaryGroupParams(['controller' => $value]);
    return $this;
  }

  public function middleware(array|string $value) : self
  {
    if(is_string($value))
      $value = [$value];

    $this->setTemporaryGroupParams(['middleware' => $value]);
    return $this;
  }

  public function withoutMiddleware(array|string $value) : self
  {
    if(is_string($value))
      $value = [$value];

    $this->setTemporaryGroupParams(['without_middleware' => $value]);
    return $this;
  }

  public function prefix(string $value) : self
  {
    $this->setTemporaryGroupParams(['prefix' => $value]);
    return $this;
  }

  public function namespace(string $value) : self
  {
    $this->setTemporaryGroupParams(['namespace' => $value]);
    return $this;
  }

  public function as(string $value) : self
  {
    $this->setTemporaryGroupParams(['as' => $value]);
    return $this;
  }

}
