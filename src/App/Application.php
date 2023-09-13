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

//  protected function setKernel() : void
//  {
//    dd(111);
//  }

  protected function setConfig() : void
  {
//    $this->config['app'] = require(CONFIG_PATH . 'app.php');;
//    dd($this->config);
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

  public function config(str $search_pattern) : array|string
  {
//    return \Arr::getByDotPattern();
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