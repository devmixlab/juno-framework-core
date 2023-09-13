<?php
namespace Juno\Database;

use Juno\Database\DB;
use Juno\Database\QueryBuilder\Builder as QueryBuilder;
use Juno\Traits\ClassDataRetrievable;

class Model{

  use ClassDataRetrievable;

  public function __construct(array $props = [])
  {
    foreach($props as $k => $v)
      $this->{$k} = $v;
  }

  public function save()
  {
    $fields = $this->getObjPublicVars();
    return DB::table($this->table)->insert($fields);
  }

  public function replace(array $data): bool
  {
    $res = DB::table($this->table)->replace($data);
    return (bool)$res;
  }

  protected function all(){
    return DB::table($this->table)->get();
  }

  protected function update(array $data)
  {
    return DB::table($this->table)->update($data);
  }

  protected function find(int $id)
  {
    return DB::table($this->table)->where('id', $id)->first();
  }

  public function getTable() : string
  {
    return $this->table;
  }

  public static function __callStatic(string $method, array $args)
  {
    if(method_exists(static::class, $method)){
      return call_user_func_array([(new static()), $method], $args);
    }else if(method_exists(QueryBuilder::class, $method)){
      $instance = new QueryBuilder((new static())->getTable());
      return call_user_func_array([$instance, $method], $args);
    }
  }

}