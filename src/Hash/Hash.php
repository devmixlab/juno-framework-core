<?php
namespace Juno\Hash;

class Hash {

  public function make(string $data){
    return password_hash($data, PASSWORD_BCRYPT);
  }

}