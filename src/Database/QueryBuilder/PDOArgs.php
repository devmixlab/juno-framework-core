<?php
namespace Juno\Database\QueryBuilder;

use Juno\Traits\ArrayListable;

class PDOArgs {

  use ArrayListable {
    hasByKey as public has;
//    get as public getArgs;
  }

  protected array $args = [];

  public function __construct()
  {
    $this->listable_property = 'args';
  }

  public function add(string $key, int|bool|string|null $value) : string|bool
  {
    $key = trim($key);
    $key = str_replace('`', '', $key);
    $origin_key = $key;

    if(!$this->has($key)){
      $this->args[$key] = $value;
    }else{
      for($i = 0; $i < 100; $i++){
        $key = $origin_key . '__' . \Str::rand(10, ['numbers','lower_letters']);
        if(!$this->has($key)){
          $this->args[$key] = $value;
          break;
        }
      }
    }

    return $key;
  }

}