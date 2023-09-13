<?php
namespace Juno\Facades;

use Juno\Database\Manager as DatabaseManager;
use Juno\Support\Facade;

class Manager extends Facade{

  public static function instanceAccessor(){
    return DatabaseManager::class;
  }

}