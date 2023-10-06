<?php
namespace Juno\Facades;

use Juno\Support\Facade;
use Juno\Hash\Hash as H;

class Hash extends Facade{

  public static function instanceAccessor(){
    return H::class;
  }

}