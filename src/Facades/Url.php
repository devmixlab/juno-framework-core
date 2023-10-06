<?php
namespace Juno\Facades;

use Juno\Support\Facade;
use Juno\Helpers\Url as U;

class Url extends Facade{

  public static function instanceAccessor(){
    return U::class;
  }

}