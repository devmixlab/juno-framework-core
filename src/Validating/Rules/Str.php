<?php
namespace Juno\Validating\Rules;

use Closure;
use Arr;

class Str extends Rule{

  public function __invoke(string $attribute, mixed $value, Closure $fail) : void
  {
//    dd($this->data);
    $exists = Arr::existsByDotPattern($this->data, $attribute);
    if(
      !$exists ||
      (
        $exists && is_string($value) && !is_numeric($value) && !empty($value)
      )
    )
      return;

    if(
      $this->settings->nullable() &&
      (
        is_null($value) ||
        (
          is_string($value) && empty($value)
        )
      )
    )
      return;

    $fail('The `:attribute` must be string.');
  }

}