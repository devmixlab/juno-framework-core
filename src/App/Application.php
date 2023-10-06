<?php
namespace Juno\App;

use App\Kernel;
use Juno\Support\ServiceProvider;
use Juno\Dotenv\Dotenv;

//use Core\App\Application;
class Application extends Container{

  protected $config = [];
//  protected $kernel = null;

  protected $registered_service_providers = [];

  public function __construct()
  {
//    $this->makeCsrf();
//    $this->kernel = new Kernel();

//    dd($this->kernel->getServiceProviders());
//    dump('__construct');
//    $this->setKernel();
//    dd(111);

    $this->registerServiceProviders();
//    dd(111);

    $this->setConfig();
//    unset(＄GLOBALS['app_config']);
  }

  public function makeCsrf() : void
  {
//    $res = md5(uniqid(mt_rand(), true));
    $res = \Hash::make(uniqid(mt_rand(), true));
    dd($res);
//    echo 'App::test';
//    die();
  }

//  protected function setKernel() : void
//  {
//    dd(111);
//  }

  protected function setConfig() : void
  {
    $this->config['app'] = require(CONFIG_PATH . 'app.php');
  }

  protected function registerServiceProviders() : void
  {
    foreach(Kernel::getServiceProviders() as $serviceProvider){
      $this->registered_service_providers[$serviceProvider] = new $serviceProvider($this);
      $this->registered_service_providers[$serviceProvider]->register();
    }
  }

  public function bootServiceProviders() : void
  {
    foreach($this->registered_service_providers as $serviceProvider)
      $serviceProvider->boot();
  }

  public function config(string $name, $value_on_empty = null): mixed {
//    dd($this->config);
    return \Arr::getByDotPattern($this->config, $name, $value_on_empty);
  }

  public function bind(string $key, $value) : void
  {
    $this->set($key, $value);
  }

  public function singleton(string $key, $value) : void
  {
    $this->set($key, $value, true);
  }

  public function make(string $id)
  {
    return $this->get($id);
  }

  public function makeWith(string $id, array $params)
  {
    return $this->get($id, $params);
  }

}