<?php
namespace Juno\Facades;

use Juno\Support\Facade;
//use Juno\Helpers\Str as StrHelper;
use Juno\Validating\Validator as Valid;

class Validator extends Facade{

  public static function instanceAccessor(){
    return Valid::class;
  }

}