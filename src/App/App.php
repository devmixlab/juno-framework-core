<?php
namespace Juno\App;

//use Core\Support\Singleton\Singleton;

//use Core\App\App;
class App extends Container{

  protected string $csrf;

//  public function __construct(){
//    parent::__construct();
//
//    $this->makeCsrf();
//  }

  public function makeCsrf() : void
  {
    dd(uniqid(mt_rand(), true));
//    echo 'App::test';
//    die();
  }
//
//  public function dispatchRouter() : void
//  {
//    echo 'App::dispatchRouter';
//    die();
//  }

}