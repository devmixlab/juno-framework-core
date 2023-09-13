<?php
namespace Juno\Sessions;

use Arr;
use Juno\Sessions\Enums\Type;
use Juno\Contracts\SessionContract;

class Session extends GlobalSession {

  public function __construct()
  {
    parent::__construct();

    $this->prefix = $this->session_key . '.' . (Type::APP)->value;
  }

//  public function put(string|array $name, $value = null) : void
//  {
//    parent::put($name, $value);
//  }
//
//  public function push(string $name, $value) : void
//  {
//    parent::push($this->makePrefixedName($name), $value);
//  }

//  public function get(string $name, $value_on_empty = null)
//  {
//    return parent::get($this->makePrefixedName($name), $value_on_empty);
//  }

//  public function forget(...$name)
//  {
//    if(!empty($name))
//      foreach($name as $arg){
//        parent::forget($this->makePrefixedName($arg));
//      }
//  }

//  public function flush()
//  {
//    parent::forget($this->makePrefixedName(''));
//  }

//  public function all()
//  {
//    if(empty($this->prefix))
//      return [];
//
//    return parent::get($this->prefix, []);
//  }

//  public function has(string $name) : bool
//  {
//    return parent::has($this->makePrefixedName($name));
//  }
//
//  public function exists(string $name) : bool
//  {
//    return parent::exists($this->makePrefixedName($name));
//  }

}