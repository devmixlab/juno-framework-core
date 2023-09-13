<?php
namespace Juno\Validating\Exceptions;

use Juno\Validating\Rules\Rule;
use Exception;

class ValidationException extends Exception
{
//  protected Route $route;
//  protected Route $route;
//  protected array $parameters;

//  public function __construct(protected Route $route, protected array $parameters = [], int $code = 400)
//  {
//    $this->message = sprintf(
//      'Missing required parameter(s) for [Route: %s] [URI: %s]',
//      $route->getName(),
//      $route->getUri()
//    );
//
//    if (!empty($parameters)) {
//      $this->message .= sprintf(' [Missing parameter(s): %s]', implode(', ', $parameters));
//    }
//
//    $this->message .= '.';
//
//    $this->code = 400;
//
////    $this->message = '';
////    $this->message .= ' ' . $this->getTraceAsString();
//
////    return $this;
//  }

//  public function __toString() : string
//  {
////    getCustomTrace
////    dd($this->getCustomTrace());
//
////    $str = core_view('exception', [
////      'exception' => self::class,
////      'message' => $this->message,
////      'code' => $this->code,
////      'trace' => $this->getCustomTrace(),
////    ])->make();
////
////    return $str;
//  }

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