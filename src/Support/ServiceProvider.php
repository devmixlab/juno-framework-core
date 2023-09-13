<?php
namespace Juno\Support;

use Juno\App\Application;

class ServiceProvider{

  public function __construct(protected Application $app){}

}