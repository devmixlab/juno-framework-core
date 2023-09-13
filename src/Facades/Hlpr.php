<?php
namespace Juno\Facades;

use Juno\Support\Facade;
use Juno\Helpers\Hlpr as H;

class Hlpr extends Facade{

  public static function instanceAccessor(){
    return H::class;
  }

}