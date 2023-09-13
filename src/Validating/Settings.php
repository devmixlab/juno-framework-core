<?php
namespace Juno\Validating;

//use Juno\Validating\Exceptions\ValidationException;
//use Juno\Validating\Rules\Required;
//use Juno\Request\Request;
//use App\Kernel;
//use Juno\Validating\Rules\Rule;
use Arr;

class Settings{

  protected bool $nullable = false;
  protected bool $bail = false;

  protected bool $has_error = false;

  static protected array $reserved = ['nullable','bail'];

  public function __construct(array|string $rules_list)
  {
    if(is_string($rules_list))
      $rules_list = explode('|', $rules_list);

    foreach(self::$reserved as $var){
      if(in_array($var, $rules_list)){
        $this->{$var} = true;
      }
    }
  }

  public function setHasError() : void
  {
    $this->has_error = true;
  }

  public function hasError() : bool
  {
    return $this->has_error;
  }

  public function nullable() : bool
  {
    return $this->nullable;
  }

  public function bail() : bool
  {
    return $this->bail;
  }

  static public function reserved() : array
  {
    return self::$reserved;
  }

}