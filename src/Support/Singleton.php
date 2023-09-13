<?php
namespace Juno\Support\Singleton;

// use Core\Support\Singleton\Singleton;
class Singleton
{
  // protected static $_instance = null;
  protected static $_instances = [];

  protected function __construct(){}

  public static function getInstance()
  {
    $called_class = get_called_class();
    if(!array_key_exists($called_class, static::$_instances))
      static::$_instances[$called_class] = new static();
    return static::$_instances[$called_class];
  }
}