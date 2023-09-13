<?php
namespace Juno\Routing;

use Juno\Support\Facade;

class RouterFacade extends Facade{

  public static function instanceAccessor(){
    return Router::class;
  }

}