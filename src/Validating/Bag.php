<?php
namespace Juno\Validating;

use Juno\Validating\Exceptions\ValidationException;
use Juno\Validating\Rules\Required;
use Juno\Request\Request;
use App\Kernel;
use Juno\Validating\Rules\Rule;
use Arr;

class Bag{

  protected $errors = [];

//  protected $reserved_rules = ['nullable','bail'];

  public function __construct(protected array $data, array $rules)
  {
    $this->rules = !empty($rules) ? $this->normalizeRules($rules) : [];
  }

  public function validate() : bool
  {
    if(empty($this->rules))
      return true;

//    dd($this->rules);

    $attribute = null;
    $fail = function(string $message) use (&$attribute) {
      if(empty($this->errors[$attribute]))
        $this->errors[$attribute] = [];

      $message = str_replace(":attribute", $attribute, $message);
      $this->errors[$attribute][] = $message;
    };

//    $go_through_name_part = function(string $part) use ($name_arr){
//
//    };

    foreach($this->rules as $name => $list){
//      $name_parts = explode('.', $name);
//      foreach($name_parts as $part){
//
//      }
//      if(count($name_arr))
//        dd(11111);
      $attribute = $name;
      $value = \Arr::getByDotPattern($this->data, $name);

//      dd($this->rules);

      foreach($list as $v)
        if($v instanceof Rule)
          $v($attribute, $value, $fail);
    }

    return empty($this->errors);
  }

  public function errors() : array
  {
    return $this->errors;
  }

  public function valid() : bool
  {
    return empty($this->errors);
  }

  protected function normalizeRules(array $rules)
  {
    $rules_normalized = [];
    foreach($rules as $k => $v){
      if(!is_string($v) && !is_array($v))
        continue;

      $list = is_string($v) ? explode('|', $v) : $v;

      $nullable = false;
      $bail = false;
      $clear_list = function(array $list) use (&$nullable, &$bail) : array {
        foreach(['nullable', 'bail'] as $var){
          if(in_array($var, $list)){
            $$var = true;
            $key = array_search($var, $list);
            unset($list[$key]);
          }
        }

        return $list;
      };
      if(is_string($list)){
        $list_arr = explode('|', $list);
        $list_arr = $clear_list($list_arr);
        $list = implode('|', $list_arr);
      }else if(is_array($list)){
        $list = $clear_list($list);
      }

      $set_rule_settings = function(Rule $rule) use ($nullable, $bail) : Rule {
        foreach(['nullable', 'bail'] as $var){
          if(!empty($$var)){
            $rule->{$var}();
          }
        }

        return $rule;
      };

      $rules_obj = [];
      foreach($list as $rule){
        if(is_object($rule) && $rule instanceof Rule){
          $rule->setData($this->data);
          $rule = $set_rule_settings($rule);
          $rules_obj[] = $rule;
        }else if(is_string($rule)){
          $rule_arr = explode(':', $rule);
          $class_alias = $rule_arr[0];
          $params = !empty($rule_arr[1]) ? explode(',', $rule_arr[1]) : [];

          $class = Kernel::getValidationRules($class_alias);
          if(empty($class))
            continue;

          $rule = new $class(...$params);
          $rule->setData($this->data);
          $rule = $set_rule_settings($rule);
          $rules_obj[] = $rule;
        }
      }

      if(!empty($rules_obj))
        $rules_normalized[$k] = $rules_obj;
    }

    $rules_normalized_finish = [];
    foreach($rules_normalized as $name => $rule){
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
      }
    }

//    dd($rules_normalized_finish);

    return $rules_normalized_finish;
  }

}