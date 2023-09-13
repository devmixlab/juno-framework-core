<?php
namespace Juno\Database;

use Juno\Database\QueryBuilder\PDOArgs;
use Juno\Database\QueryBuilder\Where;
use Juno\Facades\Manager;
use InvalidArgumentException;
use BadMethodCallException;
use \Juno\Database\QueryBuilder\Builder as QueryBuilder;

class DB{

//  protected QueryBuilder $query_builder;
//  protected PDOArgs $pdo_args;

  public function __construct(protected string $table){}

  static public function table(string $table) : self
  {
    return new self($table);
  }

  public function insert(array $data)
  {
    if(empty($data))
      throw new InvalidArgumentException();

    $first_key = array_key_first($data);
    if(!is_array($data[$first_key]))
      $data = [$data];

    $data = array_values($data);

    $keys = array_keys($data[0]);
    $keys_str = implode(', ', array_map(function($itm){
      return "{$itm}";
    }, $keys));

    $pdo_args = new PDOArgs();
    $values_arr = [];
    foreach($data as $v){
      $values_arr[] = "(" . implode(', ', array_map(function($itm) use (&$pdo_args, $v) {
        if(!$this->isTypeRight($v[$itm]))
          throw new InvalidArgumentException();

        $pdo_arg = $pdo_args->add($itm, $v[$itm]);
        return ":{$pdo_arg}";
      }, $keys)) . ")";
    }

    $values_str = implode(',', $values_arr);
    $sql = "INSERT INTO `{$this->table}` ({$keys_str}) VALUES {$values_str};";

    extract(Manager::execute($sql, $pdo_args->get()));
    return empty($res) ? false : Manager::lastInsertId();
//    return Manager::insert($sql, $pdo_args->get());
  }

  public function replace(array $data)
  {
    if(empty($data))
      throw new InvalidArgumentException();

    $pdo_args = new PDOArgs();
    $keys = array_keys($data);
    $values_str = implode(', ', array_map(function($itm) use (&$pdo_args, $data) {
      if(!$this->isTypeRight($data[$itm]))
        throw new InvalidArgumentException();

      $pdo_arg = $pdo_args->add($itm, $data[$itm]);
      return ":{$pdo_arg}";
    }, $keys));

    $sql = "REPLACE INTO `{$this->table}` VALUES ({$values_str});";

//    dump($pdo_args);
//    dd($pdo_args->get());
//    dd($sql);

    extract($this->execute($sql, $pdo_args->get()));
    return empty($res) ? false : $this->conn->lastInsertId();
//    return Manager::insert($sql, $pdo_args->get());
  }

  public function __call($method, $args = [])
  {
//    dd($method);
    if(method_exists(QueryBuilder::class, $method)){
//      dd(434);
      $query_builder = new QueryBuilder($this->table);
      return call_user_func_array([$query_builder, $method], $args);
    }

    throw new BadMethodCallException();
//    return call_user_func_array([$this, $method], $args);
  }

}