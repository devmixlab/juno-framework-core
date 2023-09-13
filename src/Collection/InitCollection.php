<?php
namespace Juno\Collection;

use Closure;
use ReflectionFunction;

class InitCollection {

  public int $test = 4234;

  static protected array $macro_methods = [];

  protected array $macro_methods_obg = [];

  public function __construct(protected array $list)
  {
    foreach(self::$macro_methods as $name => $method)
      $this->macro_methods_obg[$name] = Closure::bind($method, $this);
  }

  static public function macro(string $name, Closure $fn)
  {
    self::$macro_methods[$name] = $fn;
  }

//  protected function isTypeOf($value, string $type) : bool
//  {
//    if(is_object($value) && $value instanceof $type)
//      return true;
//
//    $type = strtolower($type);
//    if(in_array($type, ['string','int','float','bool','array'])){
//      $fn = 'is_' . $type;
//      return $fn($value);
//    }
//
//    return false;
//  }
//
//  protected function castToType($value, string $type)
//  {
//    $type = strtolower($type);
//    if($type == 'string'){
//      return (string)$value;
//    }
//  }
//
//  protected function getTypeOfClosure(Closure $fn) : array|null
//  {
//    $rf = new ReflectionFunction($fn);
//    $parameters = $rf->getParameters();
//    if(empty($parameters))
//      return null;
//
//    $parameter = $parameters[0];
//    if(!$parameter->hasType())
//      return null;
//
//    $type = $parameter->getType();
//    $type_is_builtin = $type->isBuiltin();
//
//    return [
//      "type" => (string)$type,
//      "type_is_builtin" => $type_is_builtin,
//    ];
//  }

  public function __call(string $method , array $args)
  {
    if(array_key_exists($method, $this->macro_methods_obg) && is_callable($this->macro_methods_obg[$method])){
      $list = call_user_func_array($this->macro_methods_obg[$method], $args);
      return new Collection($list->toArray());
    }
  }

}