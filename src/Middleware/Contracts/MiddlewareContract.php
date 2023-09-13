<?php
namespace Juno\Middleware\Contracts;

use Juno\Request\Request;
use Juno\Response\Response;
use Closure;

interface MiddlewareContract{

  public function handle(Request $request, Closure $next) : Response;

}