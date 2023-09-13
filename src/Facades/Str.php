<?php
namespace Juno\Facades;

use Juno\Support\Facade;
use Juno\Helpers\Str as StrHelper;

class Str extends Facade{

  public static function instanceAccessor(){
    return StrHelper::class;
  }

}