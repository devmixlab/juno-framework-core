<?php
namespace Juno\Sessions;

use Arr;
use Juno\Sessions\Enums\Type;

class FlashSession extends Session {

  protected $vars_names = [];

  public function push(string $name, $value)
  {
    if($this->exists($name))
      return;

    parent::push($name, $value);
    $this->putIntoVarsNames($name);
  }

  public function rePush(string $name)
  {
    if($this->exists($name) && !$this->inVarsNames($name))
      $this->putIntoVarsNames($name);
  }

  protected function putIntoVarsNames(string $name)
  {
    if(!in_array($name, $this->vars_names))
      $this->vars_names[] = $name;
  }

  public function inVarsNames(string $name)
  {
    return in_array($name, $this->vars_names);
  }

  public function deleteVarName(string $name)
  {
    $index = array_search($name, $this->vars_names);
    if($index !== false)
      unset($this->vars_names[$index]);
  }

}