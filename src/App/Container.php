<?php

namespace Juno\App;

use Psr\Container\ContainerInterface;
use Php\Container\Exceptions\NotFoundException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use Juno\Exceptions\BadArgumentException;

class Container implements ContainerInterface
{
  private $services = [];

  public function get(string $id, array $params = [])
  {
    $item = $this->resolve($id, $params);
    if (!($item instanceof ReflectionClass)) {
      return $item;
    }

    return $this->getInstance($item, $params);
  }

  public function has($id)
  {
    try {
      if(isset($this->services[$id]))
        return true;
      $item = new ReflectionClass($id);
    } catch (ReflectionException $e) {
      return false;
    }

    if ($item instanceof ReflectionClass) {
      return $item->isInstantiable();
    }

    return isset($item);
  }

  public function set(string $key, $value, $share = false)
  {
    $this->services[$key] = compact('value', 'share');
    return $this;
  }

  private function resolve(string $id, array $params = [])
  {
    try {
      $name = $id;
      if (isset($this->services[$id])) {
        $name = $this->services[$id];

        if (is_callable($name['value'])) {
          $reflectionFunction = new \ReflectionFunction($name['value']);
          $parameters = $reflectionFunction->getParameters();

          if(!empty($parameters)){
            $params = $this->composeParamsForReflectionFunc($reflectionFunction, $params);
//            dd($params);
            $instance = $reflectionFunction->invokeArgs($params);
          }else{
            $instance = $name['value']();
          }

          if($name['share'])
            $this->services[$id]['value'] = $instance;

          return $instance;
        }else if($name['share'] === true && !empty($name['value'])){
          return $name['value'];
        }
      }

      return (new ReflectionClass($name));
    } catch (ReflectionException $e) {
      throw new NotFoundException($e->getMessage(), $e->getCode(), $e);
    }
  }

  private function composeParamsForReflectionFunc(ReflectionFunction|ReflectionMethod $reflectionFunc, array $parameters = []) : array
  {
    $params_out = [];

    $params = $reflectionFunc->getParameters();
    if(empty($params))
      return $params_out;

    foreach ($params as $param) {
      if($param->hasType()){
        $type = $param->getType();
        $param_name = $param->getName();
        if(!$type->isBuiltin() && empty($parameters[$param_name])){
          $params_out[] = $this->get($type->getName());
        }else{
          $param_value = $parameters[$param_name] ?? null ;
          if(!$param->isOptional() && empty($param_value)){
            if($reflectionFunc instanceof ReflectionMethod){
              throw BadArgumentException::forReflectionMethodMissingArg($reflectionFunc, $param_name);
            }else{
              throw new \InvalidArgumentException("Argument `{$param_name}` is required");
            }
          }

          if($param_value !== null)
            $params_out[] = $param_value;
        }
      }
    }

    return $params_out;
  }

  private function getInstance(ReflectionClass $item, array $parameters = [])
  {
    $constructor = $item->getConstructor();
    if (is_null($constructor) || $constructor->getNumberOfRequiredParameters() == 0) {
      return $item->newInstance();
    }

    $params = $this->composeParamsForReflectionFunc($constructor, $parameters);

    return $item->newInstanceArgs($params);
  }
}