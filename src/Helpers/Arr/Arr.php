<?php
namespace Juno\Helpers\Arr;

use Closure;
use Juno\Facades\Hlpr;

class Arr{

  public function removeWithEmptyValue(array $data) : array
  {
    return array_values(array_filter($data, fn($itm) => !empty($itm)));
  }

  /*
   * Average value of a given key
   */
  public function avg(array $data, string $key = null) : float
  {
    $arr = !is_null($key) ? array_column($data, $key) : $data;
    $arr = array_filter($arr, fn($itm) => is_numeric($itm));
    if(empty($arr))
      return 0;

    return (array_sum($arr) / count($arr));
  }

  public function replace(array $data, array $replacement) : array
  {
    if(empty($data))
      return $replacement;

    foreach($replacement as $k => $v){
      if(array_key_exists($k, $data)){
        $data[$k] = $replacement[$k];
        unset($replacement[$k]);
      }
    }

    if(!empty($replacement))
      $data = array_merge($data, $replacement);

    return $data;
  }

  public function first(array $data, Closure $fn = null) : mixed
  {
    if(is_null($fn))
      return empty($data) ? null : reset($data);

    foreach($data as $k => $v){
      if($fn($v, $k))
        return $v;
    }

    return null;
  }

  public function last(array $data, Closure $fn = null) : mixed
  {
    $data = array_reverse($data, true);
    return $this->first($data, $fn);
  }

  public function forget(array $data, array|string $keys) : array
  {
    if(is_string($keys))
      $keys = [$keys];

    foreach($keys as $key)
      if(array_key_exists($key, $data))
        unset($data[$key]);

    return $data;
  }

  function map(array $data, Closure $fn) : array
  {
    foreach($data as $k => $v)
      $data[$k] = $fn($v, $k);
    return $data;
  }

  /*
   * Joins the values with a string
   */
  public function join(array $data, string $separator, string $last_separator = null) : string
  {
    $list = array_filter($data, fn($itm) => is_string($itm) || is_numeric($itm));
    if(count($list) == 0)
      return '';

    if(count($list) == 1)
      return reset($list);

    if(!is_null($last_separator))
      $last = array_pop($list);

    $str = implode($separator, $list);

    if(!empty($last))
      $str .= $last_separator . $last;

    return $str;
  }

  /*
   * Groups the items by a given key
   * If callback passed as key it should return the value to group by
   */
  public function groupBy(array $data, Closure|string|array $key, $preserveKeys = false) : array
  {
    if(!is_array($key))
      $key = [$key];

    $data = array_filter($data, function($itm){
      return is_array($itm);
    });

    $key = array_map(function($itm){
      if(is_string($itm))
        return function($v, $k) use ($itm){
          if(array_key_exists($itm, $v))
            return $v[$itm];
        };

      return $itm;
    }, $key);

    $grouped = [];
    foreach ($data as $k => $v) {
      $curr = &$grouped;
      $i = 1;
      foreach ($key as $key_k => $key_v) {
        $res = $key_v($v, $k);
        if(empty($res))
          break;

        if(!is_array($res))
          $res = [$res];

        $ii = 1;
        foreach($res as $r){
          if(!array_key_exists($r, $curr)){
            $curr[$r] = [];
          }

          if($i == count($key)){
            if($preserveKeys){
              $curr[$r][$k] = $v;
            }else{
              $curr[$r][] = $v;
            }
          }

          if($ii == count($res))
            $curr = &$curr[$r];

          $ii++;
        }

        $i++;
      }
    }

    return $grouped;
  }

  /*
   * Flattens a multi-dimensional array into a single dimension
   */
  public function flatten(array $data, int $depth = null) : array
  {
    $flatten = function(array $data, int $current_depth = 1) use ($depth, &$flatten) {
      $flattened = [];
      foreach($data as $k => $v){
        if(is_null($depth) || $current_depth <= $depth) {
          if (is_array($v)) {
            $v = $flatten($v, $current_depth + 1);
            $flattened = array_merge($flattened, $v);
          }else{
            $flattened[] = $v;
          }
        }else{
          $flattened[] = $v;
        }
      }

      return $flattened;
    };

    return $flatten($data);
  }

  /*
   * Returns the first element in the collection with the given key / value pair
   * works with two dimensional arrays
   * also can be called with a comparison operator or
   * called only with one argument
   * (will return the first item where the given item key's value is "truthy")
   */
  public function firstWhere(array $data, string $key, string $operator = null, string $value = null) : mixed
  {
    if(empty($data))
      return null;

    if(!is_null($operator) && is_null($value)){
      $value = $operator;
      $operator = null;
    }

    foreach($data as $k => $v){
      if(!is_array($v) || !array_key_exists($key, $v))
        continue;

      $vv = $v[$key];
      if(empty($value) && !empty($vv))
        return $v;

      if(!empty($value)){
        $res = match($operator) {
          '>' => $vv > $value,
          '<' => $vv < $value,
          '<=' => $vv <= $value,
          '>=' => $vv >= $value,
          '!=' => $vv != $value,
          '!==' => $vv !== $value,
          '===' => $vv === $value,
          '==' => $vv == $value,
//          default => $vv == $value,
        };

        if($res)
          return $v;
      }
    }

    return null;
  }

  public function every(array $data, Closure $fn) : bool
  {
    if(empty($data))
      return true;

    $res = $this->filter($data, $fn);
//    $res = array_filter($data, $fn);
    return count($data) == count($res);
  }

  public function contains(array $data, Closure|string $key, mixed $value = null) : bool
  {
    if(is_string($key)){
      if(!is_null($value)){
        if(empty($key))
          return false;

        if(!$this->existsByDotPattern($data, $key))
          return false;

        $val = $this->getByDotPattern($data, $key);
        return $value == $val;
      }else{
        return in_array($key, $data);
      }
    }

    if($key instanceof Closure){
      foreach($data as $k => $v){
        if($key($v, $k) === true)
          return true;
      }
    }

    return false;
  }

  public function filter(array $data, Closure $fn) : array
  {
    $filtered = [];
    foreach($data as $k => $v){
      if($fn($v, $k))
        $filtered[$k] = $v;
    }

    return $filtered;
  }

  public function reject(array $data, Closure $fn) : array
  {
    $data_new = [];
    foreach($data as $k => $v){
      if(!$fn($v, $k))
        $data_new[$k] = $v;
    }

    return $data_new;
//    return array_filter($data, function($itm) use ($fn) {
//      return !$fn($itm);
//    });
  }

  public function each(array $data, Closure $fn) : void
  {
    foreach($data as $k => $v){
      $res = $fn($v, $k);
      if($res === false)
        break;
    }
  }

  public function ensure(array $data, string|array $type) : bool
  {
    foreach($data as $k => $v){
      if(!Hlpr::isTypeOf($v, $type))
        return false;
    }

    return true;
  }

  public function countBy(array $data, Closure|string $fn = null) : array
  {
    $found = [];
    foreach($data as $k => $v){
      if(is_string($fn)){
        $str = $fn;
      }else if($fn instanceof Closure){
        $str = $fn($v);
        if(!is_string($str) || empty($str))
          continue;
      }else{
        $str = $v;
      }

      if(!array_key_exists($str, $found))
        $found[$str] = 0;

      if(is_string($fn) && $fn != $v)
        continue;

      $found[$str]++;
    }

    return $found;
  }

  public function combine(array $keys, array $values, bool $ignore_different_length = true) : array
  {
    if($ignore_different_length)
      $length = count($keys) >= count($values) ? count($values) : count($keys);

    $first = !empty($length) ? array_slice($keys, 0, $length) : $keys;
    $second = !empty($length) ? array_slice($values, 0, $length) : $values;

    return array_combine($first, $second);
  }

  public function toDot(array $data) : array
  {
    $dot_arr = [];

    $is_key = function($value){
      return !empty($value) || is_numeric($value);
    };

    $go_through = function(array $data, string $key = '') use (&$go_through, &$dot_arr, &$is_key) {
      foreach($data as $k => $v){
        $k = $is_key($key) ? $key . "." . $k : $k . '';

        if(is_array($v) && $is_key($v)){
          $go_through($v, $k);
          continue;
        }
        $dot_arr[$k] = $v;
      }
    };

    $go_through($data);

    return $dot_arr;
  }

  public function getExcept(array $data, array|string $except) : mixed
  {
    return $this->getCrossKeys($data, $except, true);
  }

  public function getOnly(array $data, array|string $only) : mixed
  {
    return $this->getCrossKeys($data, $only);
  }

  protected function getCrossKeys(array $data, array|string $keys, $reverse = false) : mixed
  {
    $out = [];
    if(empty($data))
      return $out;

    if(is_string($keys))
      $keys = [$keys];

    foreach($data as $k => $v)
      if(
        (!$reverse && in_array($k, $keys)) ||
        ($reverse && !in_array($k, $keys))
      )
        $out[$k] = $v;

    return $out;
  }

  public function getByDotPattern(array $data, string $pattern, $value_on_empty = null) : mixed
  {
    $get_value_on_empty = function() use ($value_on_empty) {
      return $value_on_empty instanceof Closure ? $value_on_empty() : $value_on_empty;
    };

    if(empty($data) || (empty($pattern) && !is_numeric($pattern)))
      return $get_value_on_empty();

    $path = $this->prepareDotPattern($pattern);
    $path_arr = explode('.', $path);

    $current = $data;
    foreach($path_arr as $k){
      if(!is_array($current))
        return $get_value_on_empty();

      if(is_numeric($k)){
        $current_indexed = array_values($current);
        $current_indexes = array_keys($current);
      }

      if(
        (!empty($current_indexed) && !array_key_exists($k, $current_indexed)) ||
        (empty($current_indexed) && !array_key_exists($k, $current))
      )
        return $get_value_on_empty();

      $current = !empty($current_indexed) ? $current[$current_indexes[$k]] : $current[$k];

      unset($current_indexed);
    }

    return $current;
  }

  public function hasByDotPattern(array $data, string|array $pattern) : bool
  {
    if(!is_array($pattern))
      $pattern = [$pattern];

    foreach($pattern as $key){
      $res = (bool)$this->getByDotPattern($data, $key);
      if(!$res)
        return false;
    }

    return true;
  }

  public function hasAnyDotPattern(array $data, string|array $pattern) : bool
  {
    if(!is_array($pattern))
      $pattern = [$pattern];

    foreach($pattern as $pat){
      if($this->hasByDotPattern($data, $pat))
        return true;
    }

    return false;
  }

  public function existsByDotPattern(array $data, string|array $pattern) : bool
  {
    if(empty($data))
      return false;

    if(!is_array($pattern))
      $pattern = [$pattern];

    foreach($pattern as $pat) {
      if(empty($pat) && !is_numeric($pat))
        return false;

      $path = $this->prepareDotPattern($pat);
      $path_arr = explode('.', $path);

      $current = $data;
      foreach ($path_arr as $k) {
        if (!is_array($current))
          return false;

        if (is_numeric($k)) {
          $current_indexed = array_values($current);
          $current_indexes = array_keys($current);
        }

        if (
          (!empty($current_indexed) && !array_key_exists($k, $current_indexed)) ||
          (empty($current_indexed) && !array_key_exists($k, $current))
        )
          return false;

        $current = !empty($current_indexed) ? $current[$current_indexes[$k]] : $current[$k];

        unset($current_indexed);
      }
    }

    return true;
  }

  public function existsAnyByDotPattern(array $data, string|array $pattern) : bool
  {
    if(!is_array($pattern))
      $pattern = [$pattern];

    foreach($pattern as $pat){
      if($this->existsByDotPattern($data, $pat))
        return true;
    }

    return false;
  }

  public function missingByDotPattern(array $data, string $pattern) : bool
  {
    return !$this->existsByDotPattern($data, $pattern);
  }

  public function setByDotPattern(array &$d, string $pattern, mixed $value, bool $by_reference = false) : array
  {
    if(empty($pattern))
      return $d;

    if($by_reference){
      $data = &$d;
    }else{
      $data = $d;
    }

    $path = $this->prepareDotPattern($pattern);

    $path_arr = explode('.', $path);
    $current = &$data;

    for($i = 0; $i < count($path_arr); $i++){
      $k = $path_arr[$i];

      if($i == count($path_arr) - 1){
        $current[$k] = $value;
        break;
      }else{
        if(!is_array($current))
          $current = [];
        if(empty($current[$k]))
          $current[$k] = [];
      }

      $current = &$current[$k];
    }

    return $data;
  }

  public function setByDotPatternRef(array $params) : array
  {
    /*
     * This way reference values in $params array will stay referenced
     * while called from inside __callStatic
     */
    return (function(array &$data, string $pattern, mixed $value){
      return $this->setByDotPattern($data, $pattern, $value, true);
    })(...$params);
  }

  public function deleteByDotPattern(array &$d, string $pattern, bool $by_reference = false) : array
  {
    if(empty($d) || empty($pattern))
      return $d;

    if($by_reference){
      $data = &$d;
    }else{
      $data = $d;
    }

    $path = $this->prepareDotPattern($pattern);

    $path_arr = explode('.', $path);
    $current = &$data;

    for($i = 0; $i < count($path_arr); $i++){
      if(!is_array($current))
        return $data;

      $k = $path_arr[$i];

      if(is_numeric($k)){
        $current_indexed = array_values($current);
        $current_indexes = array_keys($current);
      }

      if(
        (!empty($current_indexed) && !array_key_exists($k, $current_indexed)) ||
        (empty($current_indexed) && !array_key_exists($k, $current))
      )
        return $data;

      if($i == count($path_arr) - 1){
        if(!empty($current_indexed)){
          unset($current[$current_indexes[$k]]);
        }else{
          unset($current[$k]);
        }
      }else{
        if(!empty($current_indexed)){
          $current = &$current[$current_indexes[$k]];
        }else{
          $current = &$current[$k];
        }
      }

      unset($current_indexed);
    }

    return $data;
  }

  public function deleteByDotPatternRef(array $params) : array
  {
    /*
     * This way reference values in $params array will stay referenced
     * while called from inside __callStatic
     */
    return (function(array &$data, string $pattern) {
      return $this->deleteByDotPattern($data, $pattern, true);
    })(...$params);
  }

  public function prepareDotPattern(string $pattern) : string
  {
    return rtrim(ltrim($pattern, '.'), '.');
  }

  public function firstArrayOrCombineArgsIntoArray(array|string $param_1, string $param_2 = null) : ?array
  {
    if(is_array($param_1))
      return $param_1;

    if(is_string($param_1) && !empty($param_2))
      return [$param_1 => $param_2];

    return null;
  }

}