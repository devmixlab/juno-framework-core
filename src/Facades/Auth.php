<?php
namespace Juno\Facades;

use Juno\Support\Facade;
use Juno\Auth\Auth as A;

class Auth extends Facade{

  public static function instanceAccessor(){
    return A::class;
  }

}