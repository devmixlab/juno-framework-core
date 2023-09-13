<?php
namespace Juno\Routing;

use Juno\Routing\Enums\PartsTypes;
use Juno\Facades\Request;


class Part{

  protected string $name;

  protected bool $is_param = false;
  protected bool $is_param_required = false;
  protected string $param_regex;
  protected string|null $param_value;

  public function __construct(
    string $part,
    protected int $index
  ){
    $res = preg_match_all("/^{(([a-z_]+\w*)(\?)?)}$/", $part, $matches);

    if($res > 0){
      $this->name = $matches[2][0];
      $this->is_param = true;
      $this->is_param_required = empty($matches[3][0]);
    }else{
      $this->name = $part;
    }
  }

  public function setParamRegex(string $regex) : void
  {
    $this->param_regex = $regex;
  }

  public function isName(string $name) : bool
  {
    return strtolower($name) == strtolower($this->name);
  }

  public function isParamRequired() : bool
  {
    return $this->is_param_required;
  }

  public function getName() : string
  {
    return $this->name;
  }

  public function getParamValue() : string|null
  {
    return $this->param_value ?? null;
  }

  public function getIndex() : int
  {
    return $this->index;
  }

  public function isParam() : bool
  {
    return $this->is_param;
  }

  public function isMatch(string $uri_part = null) : bool
  {
    if(!$this->is_param)
      return empty($uri_part) ? false : $this->name == $uri_part;

    if(empty($uri_part))
      return !$this->is_param_required;

    if(empty($this->param_regex))
      return true;

    return (bool)preg_match($this->getFullParamRegex(), $uri_part);
  }

  public function setParamValue() : void
  {
    if($this->is_param)
      $this->param_value = Request::segments($this->index);
  }

  public function getFullParamRegex() : string|null
  {
    return !empty($this->param_regex) ? "/^" . $this->param_regex . "$/" : null;
  }

  public function isParamRegex() : bool
  {
    return !empty($this->param_regex);
  }

}
