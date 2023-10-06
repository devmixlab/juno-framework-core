<?php
namespace Juno\Validating\Rules;

use Closure;
use Arr;

class In extends Rule{

  public function __invoke(string $attribute, mixed $value, Closure $fail) : void
  {
    if(empty($this->params))
      return;

    $params = array_map(function($itm){
      return trim($itm);
    }, $this->params);

    if(!empty($value))
      $in = in_array(trim((string)$value), $params);

    if(empty($in))
      $fail('The `:attribute` must be in [' . implode(', ', array_map(function($itm){
          return '`' . $itm . '`';
        }, $params)) . '].');
  }

}