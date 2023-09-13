<?php
namespace Juno\Facades;

use Juno\Support\Facade;
use Juno\Sessions\Session as Sess;

class Session extends Facade{

  public static function instanceAccessor(){
    return Sess::class;
  }

}