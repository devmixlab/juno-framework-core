<?php
namespace Juno\Sessions;

use Arr;
use Juno\Sessions\Enums\Type;

class Session extends InitSession {

//  protected string $session_key = "juno_sessions_2zdkezuijs";
//  protected array $global_sessions;
//  protected string $prefix;

//  static protected array $registered_sessions = [];

//  public function __construct(string $prefix = null)
//  {
//    if(!empty($prefix)){
//      $this->prefix = $this->session_key . '.' . $prefix;
//      static::$registered_sessions[] = $prefix;
//    }
//
//
////    $db_driver = new DBDriver();
////    session_set_save_handler($db_driver, true);
//
//    if (session_status() == 1) {
//      session_start();
//    }
//
//    $this->global_sessions = &$_SESSION;
//
//  }

//  static public function getRegisteredSessions() : array
//  {
//    return static::$registered_sessions;
//  }

  public function put(string|array $name, $value = null) : void
  {
    if(is_string($name)){
      if(empty($value))
        return;

      $name = [$name => $value];
    }

    foreach($name as $k => $v)
      $this->push($k, $v);
  }

  public function push(string $name, $value)
  {
    $this->pushToGlobal($this->makePrefixedName($name), $value);
  }

  public function get(string $name, $value_on_empty = null)
  {
    return $this->getFromGlobal($this->makePrefixedName($name), $value_on_empty);
  }

  public function all()
  {
    return $this->getFromGlobal($this->makePrefixedName(), []);
  }

  public function forget(...$names)
  {
    if(!empty($names))
      foreach($names as $name){
        if(!$this->isPatternPartValid($name))
          return;

        $this->deleteFromGlobal($this->makePrefixedName($name));
      }
  }

  public function flush()
  {
    if($this->isPrefix()){
      $this->pushToGlobal($this->makePrefixedName(), []);
    }else {
      $this->global_sessions = array_key_exists($this->session_key, $this->global_sessions) ?
        [$this->session_key => $this->global_sessions[$this->session_key]] : [];
    }
  }

  public function increment(string $name, int $increment_by = 1) : void
  {
    $value = $this->get($name);
    if(is_numeric($value))
      $this->push($name, ($value + $increment_by));
  }

  public function decrement(string $name, int $decrement_by = 1) : void
  {
    $value = $this->get($name);
    if(is_numeric($value))
      $this->push($name, ($value - $decrement_by));
  }

  public function isEmpty(): bool {
    $arr = Arr::hasByDotPattern($this->global_sessions, $this->makePrefixedName());
    return empty($arr);
  }

  public function has(string $name) : bool
  {
    if(empty($name))
      return false;
    return Arr::hasByDotPattern($this->global_sessions, $this->makePrefixedName($name));
  }

  public function exists(string $name) : bool
  {
    return Arr::existsByDotPattern($this->global_sessions, $this->makePrefixedName($name));
  }

  public function missing(string $name) : bool
  {
    return Arr::missingByDotPattern($this->global_sessions, $this->makePrefixedName($name));
  }

  public function pull(string $name = '', $value_on_empty = null)
  {
    if($this->missing($name))
      return $value_on_empty;

    $value = $this->get($name);
    $this->forget($name);

    return $value;
  }

//  protected function isPrefix() : bool
//  {
//    return !empty($this->prefix);
//  }
//
//  protected function isPatternPartValid(null|string $name) : bool
//  {
//    return is_numeric($name) || !empty($name);
//  }
//
//  protected function getFromGlobal(string $name = null, $value_on_empty = null) : mixed
//  {
//    if(empty($name))
//      return !empty($this->global_sessions) ? $this->global_sessions : $value_on_empty;
//
//    return Arr::getByDotPattern($this->global_sessions, $name, $value_on_empty);
//  }
//
//  protected function pushToGlobal(string $name, $value)
//  {
//    Arr::setByDotPatternRef([&$this->global_sessions, $name, $value]);
//  }
//
//  protected function deleteFromGlobal(string $name)
//  {
//    Arr::deleteByDotPatternRef([&$this->global_sessions, $name]);
//  }
//
//  protected function makePrefixedName(string $name = null)
//  {
//    if(empty($this->prefix))
//      return $name;
//
//    return !$this->isPatternPartValid($name) ? $this->prefix : $this->prefix . '.' . $name;
//  }

}