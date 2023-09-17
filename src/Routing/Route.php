<?php
namespace Juno\Routing;

use Juno\Routing\Enums\GroupParams;
use Juno\Routing\Enums\Method as RouterMethod;
use Juno\Exceptions\BadRouterException;
use Juno\Routing\Enums\PartsTypes;
use Juno\Routing\Enums\CallerTypes;
use Juno\Response\Response;
use Juno\View\View;
use Juno\Request\Request;
use App\Kernel;

class Route{

  use \Juno\Traits\ParameterSetable;

  protected $uri = null;
  protected $caller_type = null;
  protected $controller = null;
  protected $action = null;
  protected $namespace = null;
  protected $middleware = [];
  protected $methods = null;
  protected $parts = [];
  protected $name = null;
  protected $controllers_default_namespace = 'App\\Controllers\\';
  protected $uri_params = [];

  protected $verbs = ['get','head','post','put','patch','delete','options'];

  public function __construct(
    string $uri,
    string|array|callable $handler,
    RouterMethod|array|null $methods = null,
    //Assigned properties
    protected array|null $groups = null,
  ){
    $this->methods = !is_array($methods) ? [$methods] : $methods;

//    dd($this->methods);
//    $this->setCaller($caller);
    $this->proccessGroups()
//      ->setCaller($caller)
      ->setHandler($handler)
      ->setUri($uri)
//      ->setUriParams();
      ->setUriParts();
  }

  public function setUriParamsValues() : self
  {
    $parts = $this->getPartsBy();
    if(empty($parts))
      return $this;

    foreach($parts as $part)
      $part->setParamValue();

    return $this;
  }

  public function resolve() : void
  {
    \Router::setCurrentRoute($this);
    $request = \App::make(Request::class);
    $kernel = \App::make(Kernel::class);
    $middleware_classes = $kernel->webMiddleware();

    $next = function(Request $request) use (&$middleware_classes, &$next) {
      if(empty($middleware_classes))
        return $this->call();

      $class = array_shift($middleware_classes);
      if(!class_exists($class))
        dd(1111);

      $middleware = new $class();
      return $middleware->handle($request, $next);
    };

    $response = $next($request);
    $response->send();
  }

  protected function call() : mixed
  {
    $params = $this->getParametersForFunc();

    if(is_callable($this->action)){
      $params = $this->addDependenciesToParams(action: $this->action, params: $params);
      $result = call_user_func_array($this->action, $params ?? []);
    }else if(
      is_string($this->controller) && !empty($this->controller) &&
      is_string($this->action) && !empty($this->action)
    ){
      if($this->caller_type == CallerTypes::AS_STRING){
        $controller = $this->controllers_default_namespace;
        if(!empty($this->namespace))
          $controller .= $this->namespace;
        $controller .= $this->controller;
        $this->controller = $controller;
      }
      $params = $this->addDependenciesToParams($this->action, $this->controller, $params);
      $result = call_user_func_array([new $this->controller, $this->action], $params ?? []);
    }

    if(!isset($result))
      return response()->text('');

    if(is_string($result) || is_numeric($result)){
      return response()
        ->text((string)$result);
    }else if(is_array($result)){
      return response()
        ->json($result);
    }else if($result instanceof Response){
      return $result;
    }else if($result instanceof View){
      return response()
        ->textHtml($result->make());
    }

  }

  protected function addDependenciesToParams(string|callable $action, string $controller = null, array $params = []) : array
  {
    $dependency_params = [];

    if(!empty($controller)) {
      $reflector = new \ReflectionClass($controller);
      $method = $reflector->getMethod($action);
    }else if(is_callable($action)){
      $method = new \ReflectionFunction($action);
    }

    if(empty($method))
      return $params;

    $method_params = $method->getParameters();
    if(!empty($method_params))
      foreach($method_params as $param){
        $dependency_param_name = $param->getName();
        if(!$param->hasType())
          continue;
        $type = $param->getType();
        if(empty($type))
          continue;
        if(!empty($dependency_param_name)){
          $dependency_param_class = $type->getName();
          $instance = \App::get($dependency_param_class);
          $dependency_params[$dependency_param_name] = $instance;
        }
      }

    return array_merge($dependency_params, $params);
  }

  protected function getParametersForFunc() : array
  {
    $parts = $this->getPartsBy();

    $params = [];
    foreach($parts as $part){
      $param_value = $part->getParamValue();
      $param_name = $part->getName();
      if(!empty($param_value))
        $params = array_merge($params, [
          $param_name => $param_value,
        ]);
    }

    return $params;
  }

  protected function proccessGroups() : self
  {
    if(empty($this->groups))
      return $this;

    $this->namespace = '';
    $this->name = '';
    $this->uri = '';
    $this->middleware = [];

    foreach($this->groups as $group){
      if($group instanceof Group){
        if($group->isParam('controller'))
          $this->controller = $group->getParam('controller');
        if($group->isParam('namespace'))
          $this->namespace .= $group->getParam('namespace');
        if($group->isParam('as'))
          $this->name .= $group->getParam('as');
        if($group->isParam('prefix'))
          $this->uri .= $group->getParam('prefix');

        if($group->isParam('middleware'))
          $this->middleware = array_unique(array_merge($this->middleware, $group->getParam('middleware')));
        if($group->isParam('without_middleware')){
          foreach($group->getParam('without_middleware') as $m){
            if(in_array($m, $this->middleware)){
              $middleware_filtered = array_filter($this->middleware, fn($v) => $v != $m);
              $this->middleware = array_values($middleware_filtered);
            }
          }
        }
      }
    }

    return $this;
  }

  public function getName() : ?string {
    return $this->name;
  }

  public function getMethods() : array
  {
    return $this->methods;
  }

  public function where(array|string $name, string $regex = null) : self
  {
    if(empty($this->parts))
      return $this;

    $name = \Arr::firstArrayOrCombineArgsIntoArray($name, $regex);

    foreach($name as $k => $v){
      $part = $this->getPartsBy(by_name: $k, first: true);

      if(empty($part))
        throw new BadRouterException("Can`t find route parameter with name - `$k`");

      $part->setParamRegex($v);
    }

//    dd(11);

    return $this;
  }

  public function getParts() : array
  {
    return $this->parts;
  }

  public function getPartsBy(string $by_name = null, bool $is_param = true, bool $first = false) : array|Part
  {
    if(empty($this->parts))
      return [];

    $parts = array_filter($this->parts, function($part) use ($by_name, $is_param) {
      $out = true;
      if(!empty($by_name))
        $out = $part->isName($by_name);
      if(!empty($is_param) && $out == true)
        $out = $part->isParam();

      return $out;
    });

    if(empty($parts))
      return [];

    $parts = array_values($parts);

    return !empty($first) ? array_shift($parts) : $parts;
  }

  public function name(string $name) : self
  {
    if(!preg_match('/^[0-9a-z_\.]+$/', $name))
        throw new BadRouterException("Wrong name format - `$name`");

//    dump(!empty($this->name) && is_string($this->name));
//    dump($name);
//    dump($this->name);
    $this->name = !empty($this->name) && is_string($this->name) ? $this->name.$name : $name;

//    dump($this->name);
    \Router::setRouteNameIndexAliasess($this);
//    dump($this->name);
    return $this;
  }

  public function middleware(array|string $value) : self
  {
    if(is_string($value))
      $value = [$value];

    foreach($value as $m){
      if(!in_array($m, $this->middleware))
        $this->middleware[] = $m;
    }

    return $this;
  }

  public function withoutMiddleware(array|string $value) : self
  {
    if(is_string($value))
      $value = [$value];

    foreach($value as $m){
      if(in_array($m, $this->middleware)){
        $middleware_filtered = array_filter($this->middleware, fn($v) => $v != $m);
        $this->middleware = array_values($middleware_filtered);
      }
    }

    return $this;
  }

  private function setUri(string $uri) : self
  {
    if($uri == '/'){
      if(empty($this->uri)) $this->uri = '/';
      return $this;
    }

//    if(preg_match("/^((\/)?(({){1}([0-9a-z_]+)((\?)?}){1}|([0-9a-z_]+)))+(\/)?$/", $uri) == 0)
    if(preg_match("/^((\/){1}(({){1}([0-9a-z_-]+)(}){1}|([0-9a-z_-]+)))+(((\/){1}({){1}[0-9a-z_-]+(\?){1}(}){1})+)?$/", $uri) == 0)
      throw new BadRouterException("Wrong route`s uri - `$uri`");

    $this->uri .= rtrim($uri, '/');

    return $this;
  }

  private function setUriParts() : self
  {
    if(empty($this->uri) || $this->uri == '/')
      return $this;

    $uri_parts = explode('/', $this->uri);
    $uri_parts = array_values(array_filter($uri_parts, fn($value) => !empty($value)));

    foreach ($uri_parts as $k => $part) {
      $this->parts[$k] = new Part(part: $part, index: $k);
    }

//    dd($this->parts);

    return $this;
  }

  private function setHandler($handler) : self
  {
    if(is_callable($handler)){
      $this->caller_type = CallerTypes::AS_CLOSURE;
      $this->action = $handler;
    }else if(is_string($handler) && !empty($this->controller)){
//      dd($this->namespace);
      $this->caller_type = CallerTypes::AS_STRING;
      $this->action = $handler;
    }else if(is_string($handler)){
      $handler_arr = explode("@", $handler);
//      dd($this->controller);
      if(count($handler_arr) != 2)
        throw new BadRouterException("Wrong handler format - `$handler`");

      $this->caller_type = CallerTypes::AS_STRING;
      $this->controller = $handler_arr[0];
      $this->action = $handler_arr[1];
    }else if(is_array($handler)){
      if(count($handler) != 2)
        throw new BadRouterException("Wrong handler format - array must have only two elements");

      $this->caller_type = CallerTypes::AS_ARRAY;
      $this->controller = $handler[0];
      $this->action = $handler[1];
    }

    return $this;
  }

  public function getController(){
    return $this->controller;
  }

  public function getAction(){
    return $this->action;
  }

  public function getUri(){
    return $this->uri;
  }

  public function isUri(string $uri){
    return $this->uri === $uri;
  }

  public function getMiddleware(){
    return $this->middleware;
  }

  public function isMatch(string $uri, RouterMethod $method) : bool
  {
    if(!in_array($method, $this->methods))
      return false;

//    $uri = preg_replace('', '', $uri);
//    dd($uri);

    if($uri == "/")
      return $this->uri == $uri;

//    $url_path_arr_all = explode('/', $uri);
    $uri_path_arr = array_values(array_filter(explode('/', $uri), fn($itm) => !empty($itm)));

//    dd($uri_path_arr);

    if(count($uri_path_arr) > count($this->parts))
      return false;

//    dd($uri_path_arr);

    foreach($this->parts as $part){
//      $part_idx = $part->getIndex();
      $uri_part = $uri_path_arr[$part->getIndex()] ?? null;
//      if($uri_part)
      if(!$part->isMatch($uri_part))
        return false;
    }

    return true;
  }

}
