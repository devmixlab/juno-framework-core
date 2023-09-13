<?php
namespace Juno\Facades;

use Juno\Support\Facade;
use Juno\Sessions\Contracts\GlobalSession as Session;

class GlobalSession extends Facade{

  public static function instanceAccessor(){
    return Session::class;
  }

}