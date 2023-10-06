<?php
namespace Juno\Facades;

use Juno\Support\Facade;
use Juno\Redirect\Redirect as R;

class Redirect extends Facade{

  public static function instanceAccessor(){
    return R::class;
  }

}