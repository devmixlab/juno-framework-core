<?php
namespace Juno\Routing;

class Group{

  protected $as = null;
  protected $prefix = null;
  protected $namespace = null;
  protected $middleware = [];
  protected $without_middleware = [];
  protected $controller = null;

  function __construct(array $params = [])
  {
    if(!empty($params))
      $this->setParams($params);
  }

  public function setParams(array $params) : void
  {
    foreach($this->getParamsData() as $p){
      if(!empty($params[$p['param']]))
        $this->{$p['set_method']}($params[$p['param']]);
    }
  }

  private function getParamsData() : array
  {
    return [
      ['param' => 'as', 'set_method' => 'setAs'],
      ['param' => 'prefix', 'set_method' => 'setPrefix'],
      ['param' => 'namespace', 'set_method' => 'setNamespace'],
      ['param' => 'middleware', 'set_method' => 'setMiddleware'],
      ['param' => 'without_middleware', 'set_method' => 'setWithoutMiddleware'],
      ['param' => 'controller', 'set_method' => 'setController'],
    ];
  }

  protected function setStringProperty(string $propertyName, string $value) : void
  {
    if(!empty($value))
      $this->{$propertyName} = $value;
  }

  protected function setArrayProperty(string $propertyName, string|array $value) : void
  {
    if(is_string($value))
      $value = [$value];

    $this->{$propertyName} = $value;
  }

  public function setController(string $value) : self
  {
    $this->setStringProperty('controller', $value);
    return $this;
  }

  public function setAs(string $value) : self
  {
    $this->setStringProperty('as', $value);
    return $this;
  }

  public function setPrefix(string $value) : self
  {
    $this->setStringProperty('prefix', $value);
    return $this;
  }

  public function setNamespace(string $value) : self
  {
    $this->setStringProperty('namespace', $value);
    return $this;
  }

  public function setMiddleware(string|array $value) : self
  {
    $this->setArrayProperty('middleware', $value);
    return $this;
  }

  public function setWithoutMiddleware(string|array $value) : self
  {
    $this->setArrayProperty('without_middleware', $value);
    return $this;
  }

  public function isParam(string|array $param) : bool
  {
    if(is_string($param))
      $param = [$param];

    $params_data = $this->getParamsData();
    $params = array_column($params_data, 'param');

    foreach($param as $p){
      if(!in_array($p, $params) || empty($this->{$p}))
        return false;
    }

    return true;
  }

  public function getParam(string $param) : string|array|null
  {
    return !$this->isParam($param) ? null : $this->{$param};
  }

}