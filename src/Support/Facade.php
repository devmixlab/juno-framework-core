<?php
namespace Juno\Support;

// use Core\support\facade\Facade;
class Facade
{
  public static function __callStatic(string $method, array $args)
  {
    global $app;

    $accessor = static::instanceAccessor();
    $instance = $app->make($accessor);

    /*
     * Some methods could have referenced arguments
     * IT JUST TO AVOID ERROR
     */
    foreach($args as $k => &$v)
      $args[$k] = &$v;

    return call_user_func_array([$instance, $method], $args);
  }
}