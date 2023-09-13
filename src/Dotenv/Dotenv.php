<?php
namespace Juno\DotEnv;

class Dotenv
{
  public function __construct(protected string $path) {}

  public function load() : void
  {
    if(!is_readable($this->path))
      throw new \RuntimeException("{$this->path} file is not readable");

    $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
      if (strpos(trim($line), '#') === 0)
        continue;

      list($name, $value) = explode('=', $line, 2);
      $name = trim($name);
      $value = trim($value);

      if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
        putenv("{$name}={$value}");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
      }
    }
  }
}