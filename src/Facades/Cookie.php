<?php
namespace Juno\Facades;

use Juno\Support\Facade;
use Juno\Cookie\Cookie as C;

class Cookie extends Facade{

  public static function instanceAccessor(){
    return C::class;
  }

}