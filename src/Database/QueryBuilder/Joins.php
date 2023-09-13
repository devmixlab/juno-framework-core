<?php
namespace Juno\Database\QueryBuilder;

use Juno\Database\QueryBuilder\Enums\Join as JoinEnum;

class Joins {

  protected string $str = '';

  public function add(JoinEnum $join, string $table, string $table_field, string $operator, string $join_table_field)
  {
    $this->str .= \Str::spaceIfNotEmpty($this->str) . "{$join->sqlStr()} {$table} ON {$table_field} {$operator} {$join_table_field}";
  }

  public function isEmpty()
  {
    return empty($this->str);
  }

  public function __toString()
  {
    return $this->str;
  }

}