<?php
namespace Juno\Request;

use Juno\Support\Facade;

class RequestFacade extends Facade{

  public static function instanceAccessor(){
    return Request::class;
  }

}