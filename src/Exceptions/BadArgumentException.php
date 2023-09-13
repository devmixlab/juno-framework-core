<?php

namespace Juno\Exceptions;

use Exception;

class BadArgumentException extends Exception
{

  public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
  {
    parent::__construct($message, $code, $previous);
  }

  public function __toString() : string
  {
    return static::class . ": {$this->message}\n";
  }

  static public function forReflectionMethodMissingArg(\ReflectionMethod $reflection, string $missing_param_name) : self
  {
    $method = $reflection->getName();
    $declaring_class = $reflection->getDeclaringClass();
    $class = $declaring_class->getName();
    return new static("Missing required argument `{$missing_param_name}` in {$class}::{$method}");
  }

}