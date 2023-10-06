<?php
namespace Juno\Validating;

use Juno\Validating\Exceptions\ValidationException;
use Juno\Validating\Rules\Required;
use Juno\Request\Request;
use App\Kernel;
use Juno\Validating\Rules\Rule;
use Arr;
use Juno\Facades\Csrf;

class Validator extends InitValidator{

  protected array $data = [];
  protected array $rules = [];
  protected array $messages = [];

  protected array $errors = [];
  protected array $validated = [];

//  protected array $reserved_rules = ['nullable','bail'];

  public function data() : array
  {
    return $this->data;
  }

  public function validated() : array
  {
    return $this->validated;
  }

  public function errors() : array
  {
    return $this->errors;
  }

  public function valid() : bool
  {
    return empty($this->errors);
  }

  public function make(array $data, array $rules, array $messages = []) : self
  {
//    dump(Csrf::get());
//    dd($data["__csrf"]);
    if(empty($data["__csrf"]) || !is_string($data["__csrf"]) || !Csrf::validate($data["__csrf"])){
      dd('Csrf is expired!');
    }

    if(empty($rules))
      return $this;

    $this->data = $data;
    [
      "normalized_rules" => $this->rules,
      "normalized_messages" => $this->messages,
    ] = $this->normalizeRules($rules, $messages);

    $settings = null;
    $attribute = null;
    $fail = function(string $message) use (&$attribute, &$settings) {
      if($settings->bail() && $settings->hasError())
        return;

      if(!empty($this->messages[$attribute]))
        $message = $this->messages[$attribute];

      if(empty($this->errors[$attribute]))
        $this->errors[$attribute] = [];

      $message = str_replace(":attribute", $attribute, $message);
      $this->errors[$attribute][] = $message;

      $settings->setHasError();
    };

    $validated = [];
    foreach($this->rules as $name => $list){
      $attribute = $name;
      $value = \Arr::getByDotPattern($this->data, $name);
      $settings = new Settings($list);

      foreach($list as $v)
        if($v instanceof Rule){
          $v->setData($this->data)->setSettings($settings);
          $v($attribute, $value, $fail);
        }

      $validated[$name] = $value;
    }

    $this->validated = $validated;

    return $this;

//    throw ValidationException::forBadRule(new Required(), 'dasd');

//    $this->count++;
//    return;

//    foreach($rules as $k => $v){
//      $groups[] = new GroupValidator($k, $v, $this->data);
////      dump($v);
//    }
//    dd($groups);

//    $rules = $this->normalizeRules($rules);
//    dd($this->rules);
//
//    $bag = new Bag($data, $rules);
//    $res = $bag->validate();
//    if(!$bag->valid())
//      dd($bag->errors());
//
////    dd(111);
//    return $bag;

//    if(empty($rules))
//      return true;
//
////    dump($rules);
//
//    $rules = $this->normalizeRules($rules);
//
//    $messages = null;
//    $attribute = null;
//    $value = null;
//    $fail = function(string $message) use (&$attribute, &$value) {
//      dump($attribute . ' - ' . $message);
//    };
//
//    foreach($rules as $name => $list){
//      $attribute = $name;
//      $value = \Arr::getByDotPattern($data, $name);
//
//      foreach($list as $v)
//        if($v instanceof Rule)
//          $v($attribute, $value, $fail);
//    }
//
//
//
//    dd($rules);
  }

  protected function normalizeRules(array $rules, array $messages = []) : array
  {
    $rules_normalized = [];
    foreach($rules as $k => $v){
      if(!is_string($v) && !is_array($v))
        continue;

      $list = is_string($v) ? explode('|', $v) : $v;

      $rules_obj = [];
      foreach($list as $rule){
        if(is_object($rule) && $rule instanceof Rule){
          $rules_obj[] = $rule;
        }else if(is_string($rule)){
          if(in_array($rule, Settings::reserved())){
            $rules_obj[] = $rule;
            continue;
          }

          $rule_arr = explode(':', $rule);
          $class_alias = $rule_arr[0];
          $params = !empty($rule_arr[1]) ? explode(',', $rule_arr[1]) : [];

          $class = Kernel::getValidationRules($class_alias);
          if(empty($class))
            continue;

          $rule = new $class(...$params);
          $rules_obj[] = $rule;
        }
      }

      if(!empty($rules_obj))
        $rules_normalized[$k] = $rules_obj;
    }

    $rules_normalized_finish = [];
    $messages_normalized = [];
    foreach($rules_normalized as $name => $rule){
      $message = $messages[$name] ?? null;
      $name_arr = explode('.', $name);
      $keys = [];

      foreach($name_arr as $k => $v){

        if($v == '*'){

          $new_keys = [];
          if(empty($keys)){
            $data_keys = array_keys($this->data);
            foreach($data_keys as $data_key){
              $new_keys[] = $data_key;
            }
          }else{
            foreach($keys as $key){
              $value = Arr::getByDotPattern($this->data, $key);
              if(!is_array($value))
                continue;

              if(empty($value)){
                $new_keys[] = $key . ".*";
              }else{
                $value_keys = array_keys($value);
                foreach($value_keys as $vv){
                  $new_keys[] = $key . "." . $vv;
                }
              }
            }
          }

          $keys = $new_keys;

        }else{

          $new_keys = [];
          if(empty($keys)){
            $new_keys = [$v];
          }else{
            foreach ($keys as $k){
              $new_k = $k . '.' . $v;
              $new_k_value = Arr::getByDotPattern($this->data, $new_k);
              if(empty($new_k_value) && !is_array($new_k_value)){
                $k_value = Arr::getByDotPattern($this->data, $k);
                if(is_array($k_value))
                  $new_keys[] = $new_k;
              }else{
                $new_keys[] = $new_k;
              }
            }
          }

          $keys = $new_keys;
        }
      }

      foreach($keys as $key){
//        $rules_normalized_finish[] = $key;
        $rules_normalized_finish[$key] = $rule;
        if(!empty($message))
          $messages_normalized[$key] = $message;
      }
    }

    return [
      "normalized_rules" => $rules_normalized_finish,
      "normalized_messages" => $messages_normalized,
    ];
  }

}