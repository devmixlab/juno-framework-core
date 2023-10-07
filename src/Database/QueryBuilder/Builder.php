<?php
namespace Juno\Database\QueryBuilder;

use Juno\Collection\Collection;
use Juno\Facades\Manager;
use Juno\Database\QueryBuilder\Enums\Join as JoinEnum;
use Closure;

class Builder {

  protected string|null $alias = null;
  protected string|null $select = null;
  protected string $having = '';
  protected string $order_by = '';
  protected string $group_by = '';

  protected Joins $joins;
  protected Where $where;
  protected PDOArgs $pdo_args;

  public function __construct(protected string $table, protected Closure|null $mod_result = null)
  {
    $this->pdo_args = new PDOArgs();
    $this->joins = new Joins();
    $this->where = new Where($this->pdo_args);
  }

  public function select(string|array $data) : self
  {
    $this->select = is_string($data) ? $data : implode(', ', $data);
    return $this;
  }

  public function alias(string $name) : self
  {
    $this->alias = $name;
    return $this;
  }

  public function join(string $table, string $table_field, string $operator, string $join_table_field) : self
  {
    $this->joins->add(JoinEnum::INNER, $table, $table_field, $operator, $join_table_field);
    return $this;
  }

  public function leftJoin(string $table, string $table_field, string $operator, string $join_table_field) : self
  {
    $this->joins->add(JoinEnum::LEFT, $table, $table_field, $operator, $join_table_field);
    return $this;
  }

  public function rightJoin(string $table, string $table_field, string $operator, string $join_table_field) : self
  {
    $this->joins->add(JoinEnum::RIGHT, $table, $table_field, $operator, $join_table_field);
    return $this;
  }

  public function crossJoin(string $table, string $table_field, string $operator, string $join_table_field) : self
  {
    $this->joins->add(JoinEnum::CROSS, $table, $table_field, $operator, $join_table_field);
    return $this;
  }

  public function where(callable|array|string $name, string $value = null) : self
  {
    $this->where->where($name, $value);
    return $this;
  }

  public function orWhere(callable|array|string $name, string $value = null) : self
  {
    $this->where->orWhere($name, $value);
    return $this;
  }

  public function whereIn(string|int $value, array $values) : self
  {
    $this->where->whereIn($value, $values);
    return $this;
  }

  public function whereNotIn(string|int $value, array $values) : self
  {
    $this->where->whereNotIn($value, $values);
    return $this;
  }

  public function orWhereIn(string|int $value, array $values) : self
  {
    $this->orWhere(function($query) use ($value, $values) {
      $query->whereIn($value, $values);
    });
    return $this;
  }

  public function orWhereNotIn(string|int $value, array $values) : self
  {
    $this->orWhere(function($query) use ($value, $values) {
      $query->whereNotIn($value, $values);
    });
    return $this;
  }

  public function orderBy(string $name, string $dir = null)
  {
    $arr = [$name];
    if(!empty($dir) && in_array(strtolower($dir), ['desc','asc']))
      $arr[] = strtoupper($dir);

    $this->order_by .= empty($this->order_by) ? 'ORDER BY ' : ', ';
    $this->order_by .= implode(' ', $arr);

    return $this;
  }

  public function reorder(string $name, string $dir = null)
  {
    $this->order_by = '';
    $this->orderBy($name, $dir);

    return $this;
  }

  public function groupBy(...$groups)
  {
    if(!empty($groups))
      $this->group_by = 'GROUP BY ' . implode(', ', $groups);

    return $this;
  }

  public function having(string $data)
  {
    $this->having = 'HAVING ' . $data;
    return $this;
  }

  public function count()
  {
    return $this->select('COUNT(*)')->fetchAgregate();
  }

  public function max(string $name)
  {
    return $this->select("MAX({$name})")->fetchAgregate();
  }

  public function min(string $name)
  {
    return $this->select("MIN({$name})")->fetchAgregate();
  }

  public function sum(string $name)
  {
    return $this->select("SUM({$name})")->fetchAgregate();
  }

  public function avg(string $name)
  {
    return $this->select("AVG({$name})")->fetchAgregate();
  }

  protected function fetchAgregate()
  {
    $sql = $this->makeSelectSql(['where']);
    return $this->pdo_args->isEmpty() ? Manager::queryColumn($sql) : Manager::fetchColumn($sql, $this->pdo_args->get());
  }

  public function update(array $data) : bool
  {
    $sql = $this->makeUpdateSql($data);
    list($res) = Manager::execute($sql, $this->pdo_args->get());

    return $res;
  }

  public function get()
  {
    $sql = $this->makeSelectSql();
    $res = $this->pdo_args->isEmpty() ?
      Manager::queryAll($sql) : Manager::fetchAll($sql, $this->pdo_args->get());

    return $this->modResult($res, 'collection');
  }

  public function first()
  {
    $sql = $this->makeSelectSql();
//    dd($this->pdo_args->get());
    $res = $this->pdo_args->isEmpty() ? Manager::querySingle($sql) : Manager::fetchSingle($sql, $this->pdo_args->get());

    return $this->modResult($res, 'model');
  }

  public function value(string $column) : string|null
  {
    $res = $this->first();
    return empty($res) || empty($res[$column]) ? null : $res[$column];
  }

  public function delete() : bool
  {
    $sql = $this->makeDeleteSql();
    ['result' => $res] = Manager::execute($sql, $this->pdo_args->get());

    return $res;
  }

  public function isExist() : bool
  {
    $res = $this->first();
    return !empty($res);
  }

  protected function modResult($result, string $type) {
    return !empty($this->mod_result) ?
      ($this->mod_result)($result, $type) : $result;
  }

  protected function makeUpdateSql(array $data) : string
  {
    $sql = $this->makeSql(['where']);

    $sets = [];
    foreach($data as $k => $v)
      $sets[] = $k . ' = :' . $this->pdo_args->add($k, $v);

    return "UPDATE {$this->table} SET " . implode(', ', $sets) . (!empty($sql) ? ' ' . $sql : '');
  }

  protected function makeDeleteSql() : string
  {
    $sql = $this->makeSql(['where']);
    return "DELETE FROM {$this->table}" . (!empty($sql) ? ' ' . $sql : '');
  }

  protected function makeSelectSql(array $what_to = []) : string
  {
    return "SELECT " . ($this->select ?? "*") . " FROM {$this->table} " . $this->makeSql($what_to);
  }

  protected function makeSql(array $what_to = []) : string
  {
    $what_to_default = ['alias','joins','where','group_by','having','order_by'];
    if(empty($what_to))
      $what_to = $what_to_default;

    $sql = '';

    if(in_array('alias', $what_to) && !empty($this->alias))
      $sql .= \Str::spaceIfNotEmpty($sql) . "{$this->alias}";

    if(in_array('joins', $what_to) && !$this->joins->isEmpty())
      $sql .= \Str::spaceIfNotEmpty($sql) . $this->joins;

    if(in_array('where', $what_to) && !$this->where->isEmpty())
      $sql .= \Str::spaceIfNotEmpty($sql) . 'WHERE ' . $this->where;

    if(in_array('group_by', $what_to) && !empty($this->group_by))
      $sql .= \Str::spaceIfNotEmpty($sql) . $this->group_by;

    if(in_array('having', $what_to) && !empty($this->having))
      $sql .= \Str::spaceIfNotEmpty($sql) . $this->having;

    if(in_array('order_by', $what_to) && !empty($this->order_by))
      $sql .= \Str::spaceIfNotEmpty($sql) . $this->order_by;

    return $sql;
  }

  public function __toString(){
    return $this->makeSelectSql();
  }

}