<?php
namespace Juno\Helpers;

class Hlpr{

  public function isTypeOf($value, string|array $type) : bool
  {
    if(!is_array($type))
      $type = [$type];

    foreach($type as $t){
      if(is_object($value) && $value instanceof $t)
        return true;

      $t = strtolower($t);
      if(in_array($t, ['string','int','float','bool','array'])){
        $fn = 'is_' . $t;
        $res = $fn($value);
        if($res === true)
          return true;
      }
    }

    return false;
  }

  protected function castToType($value, string $type)
  {
    $type = strtolower($type);
    if($type == 'string'){
      return (string)$value;
    }
  }

}