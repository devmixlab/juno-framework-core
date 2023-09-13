<?php
namespace Juno\Validating\Rules;

use Juno\Validating\Settings;

class Rule{

  protected array $params = [];
  protected array $data = [];
  protected Settings $settings;

  public function __construct(...$params)
  {
    if(!empty($params))
      $this->params = $params;
  }

  public function setData(array $data) : self
  {
    $this->data = $data;
    return $this;
  }

  public function setSettings(&$settings) : self
  {
    $this->settings = $settings;
    return $this;
  }

//  public function isNullable() : bool
//  {
//    return $this->nullable;
//  }

//  public function bail() : void
//  {
//    $this->bail = true;
//  }

}