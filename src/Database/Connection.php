<?php
namespace Juno\Database;

use PDO;
use BadMethodCallException;

class Connection{

  protected PDO $pdo;

  public function __construct(
    protected string $database,
    protected string $username,
    protected string $host = '127.0.0.1',
    protected string $port = '3306',
    protected string $password = '',
    protected string $charset = '',
    protected string $collation = ''
  ){
    $this->pdo = new PDO("mysql:host={$this->host};dbname={$this->database}", $this->username, $this->password);

    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
  }

  public function __call($method, $args = [])
  {
    if(!empty($this->pdo) && !($this->pdo instanceof PDO) || !method_exists($this->pdo, $method))
      throw new BadMethodCallException();

    return call_user_func_array([$this->pdo, $method], $args);
  }

}