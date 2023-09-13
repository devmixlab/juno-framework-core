<?php
namespace Juno\Facades;

use Juno\Support\Facade;
use Juno\Routing\Router as R;

class Router extends Facade{

  public static function instanceAccessor(){
    return R::class;
  }

}