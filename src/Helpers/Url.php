<?php
namespace Juno\Helpers;

class Url{

  public function previous(): string {
    return $_SERVER["HTTP_REFERER"];
  }

}