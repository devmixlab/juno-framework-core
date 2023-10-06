<?php
namespace Juno\Facades;

use Juno\Support\Facade;
use Juno\Helpers\Arr\Arr as A;

class Arr extends Facade{

  public static function instanceAccessor(){
    return A::class;
  }

}