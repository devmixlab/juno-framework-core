<?php
namespace Juno\Validating\Rules;

use Closure;

class Email extends Rule{

  public function __invoke(string $attribute, mixed $value, Closure $fail) : void
  {
    if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL))
      $fail('`:attribute` must be a valid email address.');
  }

}