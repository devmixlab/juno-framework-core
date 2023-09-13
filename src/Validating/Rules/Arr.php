<?php
namespace Juno\Validating\Rules;

use Closure;
use Arr as ArrHelper;

class Arr extends Rule{

  public function __invoke(string $attribute, mixed $value, Closure $fail) : void
  {
    $exists = ArrHelper::existsByDotPattern($this->data, $attribute);
    if(
      !$exists ||
      (
        $exists && is_array($value) && !empty($value)
      )
    )
      return;

    if(
      $this->settings->nullable() &&
      (
        is_null($value) ||
        (
          is_array($value) && empty($value)
        )
      )
    )
      return;

    $fail('The `:attribute` must be an array.');
  }

}