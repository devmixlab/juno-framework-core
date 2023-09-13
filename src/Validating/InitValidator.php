<?php
namespace Juno\Validating;

//use Juno\Validating\Exceptions\ValidationException;
//use Juno\Validating\Rules\Required;
//use Juno\Request\Request;

use App\Kernel;

class InitValidator{

//  protected array $rules = [];

//  public function __construct()
//  {
//    $this->rules = Kernel::getValidationRules();
//  }

//  public function make(array $data, array $rules)
//  {
////    throw ValidationException::forBadRule(new Required(), 'dasd');
//
//    if(empty($rules))
//      return true;
//
//    foreach($rules as $name => $rule){
//      if(is_string($rule)){
//        $this->validateStringTypeRule($name, $rule);
//
//      }
//    }
//
//    dd(222);
//  }

  protected function validateStringTypeRule(string $name, string $rule)
  {
    $rules = explode('|', $rule);

    foreach($rules as $v){
      $reference = preg_replace('/(:)(\w)*/i', '', trim($v));
      if(empty($this->rules[$reference]))
        throw ValidationException::forBadRuleName($reference);

      $class = $this->rules[$reference];

      $request = \App::make(Request::class);
      $instance = new $class();
      $instance->validate($request, $name);
      dd($class);
    }

//    if(count($rules) == 1){
//
//    }else{
//      foreach($rules_arr as $rule){
//        $rules_arr = explode(':', $rule);
//        if(count($rules_arr) > 2)
//          throw ValidationException::forBadRule(new Required(), $rule);
//        Exceptions
//          }
//      dd($rules_arr);
//    }
}

}