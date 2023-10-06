<?php
namespace Juno\Validating\Rules;

use Closure;
use Arr;

class Same extends Rule{

  public function __invoke(string $attribute, mixed $value, Closure $fail) : void
  {
    if(empty($this->params))
      return;

    $compare_to_attribute = array_shift($this->params);
    $compare_to = Arr::getByDotPattern($this->data, $compare_to_attribute);
    if($value != $compare_to)
      $fail("The `:attribute` must be same as `{$compare_to_attribute}`.");
  }

}