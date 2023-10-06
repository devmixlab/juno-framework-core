<?php
namespace Juno\Validating\Rules;

use Closure;
use Arr;

class Numeric extends Rule{

  public function __invoke(string $attribute, mixed $value, Closure $fail) : void
  {
//    $exists = Arr::existsByDotPattern($this->data, $attribute);
//    dd($value);
    if(!empty($value) && !is_numeric($value))
      $fail('The `:attribute` must be a number.');
  }

}