<?php
namespace Juno\App;

use Juno\Support\Facade;

class ApplicationFacade extends Facade{

  public static function instanceAccessor(){
    return 'app';
//    global $app;
//    return $app;
//    return Application::class;
  }

}