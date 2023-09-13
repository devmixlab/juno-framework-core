<?php
namespace Juno\Traits;

// use Juno\Traits\ParameterSetable;
trait ParameterSetable
{
  public function combineArgsIntoArray(array|string $param_1, string $param_2 = null) : ?array
  {
    if(is_string($param_1) && empty($param_2))
      return null;

    if(is_string($param_1) && !empty($param_2))
      return [$param_1 => $param_2];

    if(is_array($param_1))
      return $param_1;

    return null;
  }
}