<?php
namespace Juno\Database\QueryBuilder;

class Where{

  protected string $str = '';

  public function __construct(protected PDOArgs &$pdo_args){}

  public function isEmpty() : bool
  {
    return empty($this->str);
  }

//  static public function availableWhereMethods()
//  {
//    return ['where','orWhere','whereIn','whereNotIn','orWhereIn','orWhereNotIn'];
//  }

  public function where(callable|array|string $name, string $value = null) : self
  {
    $this->setWhere($name, $value);
    return $this;
  }

  public function orWhere(callable|array|string $name, string $value = null) : self
  {
    $this->setWhere($name, $value, 'or');
    return $this;
  }

  public function whereIn(string|int $value, array $values) : self
  {
    $this->setWhereIn($value, $values);
    return $this;
  }

  public function whereNotIn(string|int $value, array $values) : self
  {
    $this->setWhereIn($value, $values, false);
    return $this;
  }

  public function setWhereIn(string|int $value, array $values, bool $in = true, string $operator = 'and')
  {
    if(empty($values))
      return $this;

    $operator = strtolower($operator) == 'and' ? ' AND ' : ' OR ';
    if(!empty($this->str) && str_ends_with($this->str, ')'))
      $this->str .= $operator;

    $value = trim($value);

    $in_arr = [];
    $pdo_keys = [];
    $i = 1;
    foreach($values as $v){
      if(is_string($v))
        $v = "'$v'";
      if(is_numeric($v))
        $v = (int)$v;
      $pdo_keys[] = ':' . $this->pdo_args->add($value . '_' . $i, $v);
      $i++;
    }

    $this->str .= "({$value} " . (!$in ? 'NOT ' : '') . "IN (" . implode(', ', $pdo_keys) . "))";
  }

  public function __toString()
  {
    return $this->str;
  }

  protected function setWhere(callable|array|string $name, string $value = null, string $operator = 'and')
  {
    $operator = strtolower($operator) == 'and' ? ' AND ' : ' OR ';
    if(!empty($this->str) && str_ends_with($this->str, ')'))
      $this->str .= $operator;

    if(is_callable($name)){
      $this->str .= '(';
      $name($this);
      $this->str .= ')';
      return $this;
    }

    $params = $this->mapArgsToParams($name, $value);
    if(empty($params))
      return $this;

    $this->str .= '(' . $this->makeString($params) . ')';
  }

  protected function makeString(array $data) : string|bool
  {
    $arr = [];
    if(empty($data))
      return false;

    if(!is_array($data[0]))
      $data = [$data];

    foreach($data as $v){
      if(count($v) != 3 || empty($v[0]))
        continue;

      $key = trim($v[0]);
      $operator = $v[1] ?? null;
      $value = $v[2] ?? null;
      $pdo_key = $this->pdo_args->add($key, $value);

      $arr[] = "{$key} {$operator} :{$pdo_key}";
    }

    $str = implode(' AND ', $arr);

    return $str;
  }

  protected function mapArgsToParams(array|string $name, string $value = null) : array|bool
  {
    if(is_string($name) && is_string($value)){
      return [$name, '=', $value];
    }else if(is_array($name)){
      if(is_array($name[0])){
        $name = array_filter($name, fn($itm) => is_array($itm) && (count($itm) == 2 || count($itm) == 3));
        foreach($name as $k => $v){
          if(count($v) == 2)
            $name[$k] = [$v[0], '=', $v[1]];
        }
      }else if(is_string($name[0])){
        if(count($name) == 2)
          $name = [$name[0], '=', $name[1]];
      }else{
        return false;
      }
    }else{
      return false;
    }

    return $name;
  }

}