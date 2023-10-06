<?php
namespace Juno\Auth;

class InitAuth {

  protected $providers;

  function __construct() {
//    $this->providers_conf = \App::config("auth.providers");
//    $this->token_expiration_interval = \App::config("security.token_expiration_interval");
//
//    $this->setInitialAuthorizedByGuard();
  }

  public function provider(string $name): null|Provider {
    if(empty($this->providers[$name])){
      $config = \App::config("app.auth.providers." . $name);
      if(!$config)
        return null;

      $this->providers[$name] = new Provider($config);
    }

    return $this->providers[$name];
  }

}