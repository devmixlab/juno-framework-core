<?php
namespace Juno\App;

use Juno\Support\Facade\Facade;

class AppFacade extends Facade{

  public static function getClassInstance(){
    return App::getInstance();
  }

}