<?php
namespace Juno\Facades;

use Juno\Support\Facade;
use Juno\Validating\Csrf as C;

class Csrf extends Facade{

  public static function instanceAccessor(){
    return C::class;
  }

}