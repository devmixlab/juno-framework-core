<?php
namespace Juno\Traits;

trait ClassDataRetrievable
{
  public function getObjPublicVars()
  {
    // PHP 8.1
    return get_object_vars(...)->__invoke($this);

//    // PHP 7.1
//    return \Closure::fromCallable("get_object_vars")->__invoke($this);

//    // PHP 7.0
//    return (function($object){return get_object_vars($object);})->bindTo(null, null)($this);
  }
}