<?php
namespace Juno\Request\Data;

use Arr;

class Data
{
  protected array $data = [];

  public function has(string $name) : bool
  {
    if(empty($name))
      return !empty($this->data);

    return Arr::hasByDotPattern($this->data, $name);
  }

  public function hasAny() : bool
  {
    return !empty($this->data);
  }

  public function get(string $name, $value_on_empty = null) : mixed
  {
    if(empty($name))
      return $value_on_empty;

    return Arr::getByDotPattern($this->data, $name, $value_on_empty);
  }

  public function all() : array
  {
    return $this->data;
  }

  public function only(string|array $only) : array
  {
    return Arr::getOnly($this->data, $only);
  }

  public function except(string|array $except) : array
  {
    return Arr::getExcept($this->data, $except);
  }

}