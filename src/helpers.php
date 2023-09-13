<?php
use Juno\Request\Request;
use Juno\Response\Response;
use Juno\View\View;


if(!function_exists('abort')){
  function abort(int $code){
    $view = core_view('404')->make();
    echo $view;
    exit;
//    response()->textHtml()
  }
}

if(!function_exists('view')){
  function view(string $path, array $params = []){
//    dd(111);
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