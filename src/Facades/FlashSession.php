<?php
namespace Juno\Facades;

use Juno\Support\Facade;
use Juno\Sessions\Contracts\FlashSessionContract as Session;

class FlashSession extends Facade{

  public static function instanceAccessor(){
    return Session::class;
  }

}