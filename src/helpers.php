<?php
use Juno\Request\Request;
use Juno\Response\Response;
use Juno\View\View;
use Juno\Redirect\Redirect;

use Juno\Sessions\Session;
use Juno\Sessions\FlashSession;
use Juno\Sessions\Contracts\GlobalSession;
use Juno\Sessions\Contracts\AuthSession;
use Juno\Sessions\Contracts\FlashSessionContract;
use Juno\Sessions\Contracts\AppSession;

if (! function_exists('old')) {
  function old(string $dotted_name, $value_on_empty = null){
    $dotted_name = 'input.' . $dotted_name;
    return flash_session()->exists($dotted_name) ?
      flash_session()->get($dotted_name) : $value_on_empty;
  }
}

if (! function_exists('global_session')) {
  function global_session(){
    return \App::make(GlobalSession::class);
  }
}

if (! function_exists('auth_session')) {
  function auth_session(){
    return \App::make(AuthSession::class);
  }
}

if (! function_exists('flash_session')) {
  function flash_session(){
    return \App::make(FlashSessionContract::class);
  }
}

if (! function_exists('app_session')) {
  function app_session(){
    return \App::make(AppSession::class);
  }
}

if (! function_exists('redirect')) {
  function redirect(){
    return \App::make(Redirect::class);
  }
}

if (! function_exists('route')) {
  function route(string $route_name, array $params = [], bool $absolute = false){
    return \Router::getUrlByRouteName($route_name, $params, $absolute);
  }
}

if (! function_exists('is_current_route')) {
  function is_current_route(string $route_name){
    return $route_name == \Router::getCurrentRouteName();
  }
}

if(!function_exists('abort')){
  function abort(int $code){
    $view = core_view('404')->make();
    echo $view;
    exit;
//    response()->textHtml()
  }
}

if(!function_exists('view')){
//  throw new \Juno\Exceptions\ViewException("Wrong content");
  function view(string $path, array $params = []){
//    dump(111);
//    throw new \Juno\Exceptions\ViewException("Wrong content");
//    return new View($path, $params);
    return \App::makeWith(View::class, [
      'path' => $path,
      'params' => $params,
//      'is_core' => false,
    ]);
  }
}

if(!function_exists('core_view')){
  function core_view(string $path, array $params = []){
    return \App::makeWith(View::class, [
      'path' => $path,
      'params' => $params,
      'is_core' => true,
    ]);
  }
}

if(!function_exists('request')){
  function request(){
    return \App::make(Request::class);
  }
}

if(!function_exists('response')){
  function response(){
    return \App::make(Response::class);
  }
}

if(!function_exists('dd')){
  function dd(){
    pr(func_get_args());
    die();
  }
}

if(!function_exists('ddh')){
  function ddh($html){
    echo '<pre>';
    echo htmlentities($html);
    echo '</pre>';
    die();
  }
}

if(!function_exists('dump')) {
  function dump()
  {
    pr(func_get_args());
  }
}

if(!function_exists('pr')) {
  function pr($args)
  {
    echo "<pre>";
    foreach ($args as $arg) {
      var_dump($arg);
    }
    echo "<pre>";
  }
}

//if(!function_exists('sl')) {
//  function sl($var, $file_name_to_save = '/dump.txt')
//  {
//    file_put_contents($_SERVER['DOCUMENT_ROOT'] . $file_name_to_save, json_encode($var));
//  }
//}
//
//if(!function_exists('sls')) {
//  function sls($string, $file_name_to_save = '/dump.txt')
//  {
//    file_put_contents($_SERVER['DOCUMENT_ROOT'] . $file_name_to_save, $string);
//  }
//}

?>