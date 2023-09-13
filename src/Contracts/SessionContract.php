<?php
namespace Juno\Contracts;

//use Core\App\Application;

interface SessionContract{

  public function put(string|array $name, $value = null) : void;
  public function push(string $name, $value) : void;
  public function get(string $name, $value_on_empty = null);
  public function forget(...$name);
  public function flush();
  public function all();

}