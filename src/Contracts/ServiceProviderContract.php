<?php
namespace Juno\Contracts;

//use Core\App\Application;

interface ServiceProviderContract{

  public function register() : void;

  public function boot() : void;

}