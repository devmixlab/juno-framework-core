<?php
namespace Juno\Database;

use PDO;

class Manager{

  public function __construct(protected Connection $conn) {}

  public function isTypeRight($val)
  {
    return is_string($val) || is_int($val) || is_bool($val) || is_null($val);
  }

  public function isConn() : bool
  {
    return !empty($this->conn);
  }

  public function queryAll(string $sql)
  {
    $statement = $this->conn->query($sql);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
  }

  public function querySingle(string $sql)
  {
    $statement = $this->conn->query($sql);
    return $statement->fetch();
  }

  public function queryColumn(string $sql)
  {
    $statement = $this->conn->query($sql);
    return $statement->fetchColumn();
  }

  public function fetchAll(string $sql, array $args)
  {
    ['statement' => $st] = $this->execute($sql, $args);
    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  public function fetchSingle(string $sql, array $args)
  {
    ['statement' => $st] = $this->execute($sql, $args);
    return $st->fetch(PDO::FETCH_ASSOC);
  }

  public function fetchColumn(string $sql, array $args)
  {
    ['statement' => $st] = $this->execute($sql, $args);
    return $st->fetchColumn();
  }

  public function execute(string $sql, array $args = []) : array
  {
    $statement = $this->conn->prepare($sql);
    $result = $statement->execute($args);

    return ['result' => $result, 'statement' => $statement];
  }

  public function transaction(string $sql, array $args) : bool
  {
    $this->conn->beginTransaction();

    extract($this->execute($sql, $args));
    ['statement' => $st, 'result' => $res] = $this->execute($sql, $args);

    $st->closeCursor();
    $this->conn->commit();

    return !empty($res);
  }

  public function lastInsertId() : int
  {
    if($this->isConn())
      return 0;

    return $this->conn->lastInsertId();
  }

//  protected function modifyResult($data) {
//
//  }

//  public function __call (string $name , array $arguments): mixed {
//    if(in_array($name, ['querySingle','fetchSingle'])){
//      $res = call_user_func_array([$this, $name], $arguments);
//      if()
//      return $res;
//    }
//  }

}