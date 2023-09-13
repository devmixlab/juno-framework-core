<?php
namespace Juno\Traits;

trait ArrayListable
{
  protected $listable_property = 'list';

  public function isEmpty() : bool
  {
    return empty($this->{$this->listable_property});
  }

  public function hasByKey(string $key) : bool
  {
    return !$this->isEmpty() && !empty($this->{$this->listable_property}[$key]);
  }

  public function get() : array|null
  {
    return !$this->isEmpty() ? $this->{$this->listable_property} : null;
  }
}