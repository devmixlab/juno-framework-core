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
    $res = DB::table($this->table)->get();
    return static::modQueryBuilderResult($res, 'collection');
  }

  protected function update(array $data)
  {
    return DB::table($this->table)->update($data);
  }

  protected function find(int $id)
  {
    $res = DB::table($this->table)->where('id', $id)->first();
    return static::modQueryBuilderResult($res, 'model');
  }

  public function getTable() : string
  {
    return $this->table;
  }

  public function toArray(): array {
    $vars = $this->getObjPublicVars();
    if(!empty($this->hidden) && is_array($this->hidden)){
      foreach($this->hidden as $hidden){
        if(array_key_exists($hidden, $vars))
          unset($vars[$hidden]);
      }
    }

    return $vars;
  }

  static protected function modQueryBuilderResult($result, string $type){
    if(is_array($result)){
      if($type == 'model'){
        return new static($result);
      }else if($type == 'collection'){
        return (collect($result))->mapInto(static::class);
      }
    }

    return $result;
  }

  public static function __callStatic(string $method, array $args)
  {
    if(method_exists(static::class, $method)){
      return call_user_func_array([(new static()), $method], $args);
    }else if(method_exists(QueryBuilder::class, $method)){
      $instance = new QueryBuilder((new static())->getTable(), function($result, string $type) {
        return static::modQueryBuilderResult($result, $type);
      });

      return call_user_func_array([$instance, $method], $args);
    }
  }

}