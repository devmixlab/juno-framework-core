<?php
namespace Juno\Exceptions;

use Juno\Routing\Route;

class UrlGenerationException extends AppException
{
//  protected Route $route;
  protected Route $route;
  protected array $parameters;

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

  public function __toString() : string
  {
//    getCustomTrace
//    dd($this->getCustomTrace());

    $str = core_view('exception', [
      'exception' => self::class,
      'message' => $this->message,
      'code' => $this->code,
      'trace' => $this->getCustomTrace(),
    ])->make();

    return $str;
  }

  static public function forMissingParameters(Route $route, array $parameters = [], int $code = 0)
  {
    $exc = new self();
    $exc->message = sprintf(
      'Missing required parameter(s) for [Route: %s] [URI: %s]',
      $route->getName(),
      $route->getUri()
    );

    if (!empty($parameters))
      $exc->message .= sprintf(' [Missing parameter(s): %s]', implode(', ', $parameters));

    $exc->message .= '.';

    $exc->code = $code;

    return $exc;
  }

  static public function forWrongParameters(Route $route, array $parameters = [], int $code = 0)
  {
    $exc = new self();
    $exc->message = sprintf(
      'Wrong parameter(s) values for [Route: %s] [URI: %s]',
      $route->getName(),
      $route->getUri()
    );

    if (!empty($parameters))
      $exc->message .= sprintf(' [Wrong value(s) for parameter(s): %s]', implode(', ', $parameters));

    $exc->message .= '.';

    $exc->code = $code;

    return $exc;
  }

}