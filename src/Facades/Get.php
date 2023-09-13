<?php
namespace Juno\Facades;

use Juno\Support\Facade;
use Juno\Request\Data\Get as G;

class Get extends Facade{

  public static function instanceAccessor(){
    return G::class;
  }

}