<?php
namespace Juno\Collection;

use Closure;
use ReflectionFunction;

class Chunks {

  protected array $list = [];

  public function __construct(Collection|array $data, int $length)
  {
    if($data instanceof Collection)
      $data = $data->all();
    $arr = array_chunk($data, $length);
    foreach ($arr as $chunk)
      $this->list[] = new Collection($chunk);
  }

  public function all() : array
  {
    return $this->list;
  }

}