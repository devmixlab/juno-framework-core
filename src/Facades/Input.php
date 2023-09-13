<?php
namespace Juno\Facades;

use Juno\Support\Facade;
use Juno\Request\Data\Input as I;

class Input extends Facade{

  public static function instanceAccessor(){
    return I::class;
  }

}