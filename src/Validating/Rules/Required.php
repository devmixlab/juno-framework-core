<?php
namespace Juno\Validating\Rules;

use Closure;
use Arr;

class Required extends Rule{

  public function __invoke(string $attribute, mixed $value, Closure $fail) : void
  {
    if(
      (
        $this->settings->nullable() &&
        !Arr::existsByDotPattern($this->data, $attribute)
      ) ||
      (
        !$this->settings->nullable() &&
        empty($value) &&
        !is_numeric($value) && !is_bool($value)
      )
    ){
      $fail('The `:attribute` is required.');
    }
  }

}