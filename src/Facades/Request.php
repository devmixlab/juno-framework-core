<?php
namespace Juno\Facades;

use Juno\Support\Facade;
use Juno\Request\Request as Req;

class Request extends Facade{

  public static function instanceAccessor(){
    return Req::class;
  }

}