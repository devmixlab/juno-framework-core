<?php
namespace Juno\Exceptions;

class ViewException extends AppException
{

  static public function forWrongPath(string $path, array $parameters = [], int $code = 0)
  {

//    dd($path);
    $exc = new self();
    $exc->message = "View does not exists [View: {$path}]";
//    $exc->message = sprintf(
//      'View does not exists [View: %s]',
//      $path
//    );

//    dd($exc->message);

//    if (!empty($parameters))
//      $exc->message .= sprintf(' [Missing parameter(s): %s]', implode(', ', $parameters));

    $exc->message .= '.';

//    $exc->code = $code;

    return $exc;
  }

}