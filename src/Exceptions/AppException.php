<?php

namespace Juno\Exceptions;

use Exception;

class AppException extends Exception
{

  public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
  {
    parent::__construct($message, $code, $previous);
  }

  public function __toString() : string
  {
    return static::class . ": {$this->message}\n";
  }

  public function getCustomTrace() : string
  {
    $trace_str = '';
    $trace_arr = $this->getTrace();

    if(empty($trace_arr))
      return $trace_str;

    $first_trace = array_shift($trace_arr);

    dd($trace_arr);
  }

}