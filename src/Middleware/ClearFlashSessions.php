<?php
namespace Juno\Middleware;

use Juno\Request\Request;
use Juno\Response\Response;
use Juno\Middleware\Contracts\MiddlewareContract;
use Closure;
use Juno\Facades\FlashSession;

class ClearFlashSessions implements MiddlewareContract{

  public function handle(Request $request, Closure $next) : Response
  {
    $response = $next($request);

    foreach(FlashSession::all() as $k => $v){
      if(FlashSession::inVarsNames($k)){
        FlashSession::deleteVarName($k);
      }else{
        FlashSession::forget($k);
      }
    }

    return $response;
  }

}