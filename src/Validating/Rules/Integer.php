<?php
namespace Juno\Validating\Rules;

use Closure;
use Arr;

class Integer extends Rule{

  public function __invoke(string $attribute, mixed $value, Closure $fail) : void
  {
    if(!empty($value) && !filter_var($value, FILTER_VALIDATE_INT))
      $fail('The `:attribute` must be an integer.');
  }

}