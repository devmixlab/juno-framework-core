<?php
namespace Juno\Helpers\Arr;

use Juno\Support\Facade;

class ArrFacade extends Facade{

  public static function instanceAccessor(){
    return Arr::class;
  }

}