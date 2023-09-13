<?php
namespace Juno\Facades;

use Juno\Support\Facade;
use Juno\Sessions\Contracts\AppSession as Session;

class AppSession extends Facade{

  public static function instanceAccessor(){
    return Session::class;
  }

}