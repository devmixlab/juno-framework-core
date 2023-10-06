<?php
namespace Juno\Auth;

class InitAuth {

  protected $guards;

  function __construct() {
//    $this->guards = \App::config("auth.guards");
//    $this->token_expiration_interval = \App::config("security.token_expiration_interval");
//
//    $this->setInitialAuthorizedByGuard();
  }

  static public function hash(string $data): string {
    return password_hash($data, PASSWORD_BCRYPT);
  }

}