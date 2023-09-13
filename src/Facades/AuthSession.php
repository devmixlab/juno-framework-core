<?php
namespace Juno\Facades;

use Juno\Support\Facade;
use Juno\Sessions\Contracts\AuthSession as Session;

class AuthSession extends Facade{

  public static function instanceAccessor(){
    return Session::class;
  }

}