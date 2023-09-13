<?php
namespace Juno\Routing;

use Juno\Routing\Enums\Method as RouterMethod;
use Juno\Exceptions\BadRouterException;

class MainRouter{

  protected ?Group $temporary_group = null;
  protected array $groups = [];
  protected array $routes = [];
  protected array $route_name_index_aliasess = [];
  protected Route $current_route;

  public function __construct()
  {
//    dump('__construct');
//    $this->routes = new Routes();
  }

  protected function proccessGroupCallback(callable $func) : void
  {
    $this->groups[] = $this->temporary_group;
    $this->temporary_group = null;
    $func();
    array_pop($this->groups);
  }

  protected function setTemporaryGroupParams(array $params) : void
  {
    if(empty($this->temporary_group)){
      $this->temporary_group = new Group($params);
    }else{
      $this->temporary_group->setParams($params);
    }
  }

  protected function add(
    string $uri,
    string|array|callable $handler,
    RouterMethod|array|null $methods = null
  ) : Route
  {
    if(is_array($methods) && !RouterMethod::isAllValuesEnums($methods))
      throw new BadRouterException('Supplied wrong list of methods to route: ' . json_encode($methods));

    $route = new Route(uri: $uri, handler: $handler, methods: $methods, groups: $this->groups);
    $this->routes[] = $route;

    return $route;
  }

  protected function getRouteByName(string $route_name) : Route|null
  {
    if(!isset($this->route_name_index_aliasess[$route_name]))
      return null;

    $idx = $this->route_name_index_aliasess[$route_name];
    return $this->routes[$idx] ?? null;
  }

}
