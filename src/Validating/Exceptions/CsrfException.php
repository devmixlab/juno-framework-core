<?php
namespace Juno\Validating\Exceptions;

use Juno\Validating\Rules\Rule;
use Exception;

class CsrfException extends Exception{

  static public function forBadRule(Rule $rule, string $definition = null) : self
  {
    $exception = new self();

    $exception->message = sprintf(
      'Wrong rule definition [Rule: %s]',
      get_class($rule)
    );

    if (!empty($definition))
      $exception->message .= sprintf(' [Definition: %s]', $definition);

    $exception->message .= '.';

//    $exc->code = $code;

    return $exception;
  }

  static public function forBadRuleName(string $rule_name) : self
  {
    $exception = new self();

    $exception->message = sprintf(
      'Wrong rule name [Name: %s]',
      $rule_name
    );

    return $exception;
  }

}