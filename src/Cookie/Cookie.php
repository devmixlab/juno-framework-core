<?php
namespace Juno\Cookie;

use Arr;

class Cookie {

  protected array $data;

  public function __construct()
  {
    $this->data = $_COOKIE;
  }

  public function set(
    string $name,
    string $value = "",
    int $expires_or_options = 0,
    string $path = "/",
    string $domain = "",
    bool $secure = false,
    bool $httponly = false
  ) : void
  {
    setcookie($name, $value, $expires_or_options, $path, $domain, $secure, $httponly);
  }

  public function has(string $cookie_path) : bool
  {
    return Arr::hasByDotPattern($this->data, $cookie_path);
  }

  public function exists(string $cookie_path) : bool
  {
    return Arr::existsByDotPattern($this->data, $cookie_path);
  }

  public function get(string $path, $value_on_empty = null)
  {
    return Arr::getByDotPattern($this->data, $path, $value_on_empty);
  }

  public function forget(string|array $names) : void
  {
    if(is_string($names))
      $names = [$names];

    foreach($names as $name)
      $this->set($name, false);
  }

}