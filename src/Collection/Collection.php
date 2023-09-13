<?php
namespace Juno\Collection;

use Closure;
use Arr;

class Collection extends InitCollection {

  /*
   * LEFT TO IMPLEMENT:
   *
   * flip, forPage, implode, intersect, intersectAssoc,
   * intersectByKeys, mapSpread, mapToGroups, mapWithKeys,
   * max, median, merge, mergeRecursive, min, mode, nth, only,
   * pad, partition, percentage, pipe, pipeInto, pipeThrough,
   * pluck, pop, prepend, pull, push, put, random, range,
   * reduce, reduceSpread, replaceRecursive, reverse, search,
   * shift, shuffle, skip, skipUntil, skipWhile, slice, sliding,
   * sole, some(contains), sort, sortBy, sortByDesc, sortDesc,
   * sortKeys, sortKeysDesc, sortKeysUsing, splice, split, splitIn,
   * sum, take, takeUntil, takeWhile, tap, times, toArray, toJson,
   * undot, union, unique, uniqueStrict, unless, unlessEmpty(whenNotEmpty),
   * unlessNotEmpty(whenEmpty), unwrap, value, values, when, whenEmpty, whenNotEmpty,
   * where, whereStrict, whereBetween, whereIn, whereInStrict, whereInstanceOf,
   * whereNotBetween, whereNotIn, whereNotInStrict, whereNotNull, whereNull,
   * wrap, zip
   */

  public function map(Closure $fn) : Collection
  {
    $list = array_map($fn, $this->list);
    return new Collection($list);
  }

  public function transform(Closure $fn) : Collection
  {
    $this->list = array_map($fn, $this->list);
    return $this;
  }

  public function reject(Closure $fn) : Collection
  {
    $res = Arr::reject($this->list, $fn);
    return new Collection($res);
  }

  public function toArray() : array
  {
    return $this->list;
  }

  public function all() : array
  {
    return $this->list;
  }

  public function avg(string $key) : float
  {
    $arr = array_filter(array_column($this->list, $key), fn($itm) => is_numeric($itm));
    if(empty($arr))
      return 0;

    return (array_sum($arr) / count($arr));
  }

  public function chunk(int $length) : Chunks
  {
    return new Chunks($this, $length);
  }

  public function collapse() : Collection
  {
    return new Collection(Arr::collapse($this->list));
  }

  public function collect() : Collection
  {
    return new Collection($this->list);
  }

  public function combine(array $values, bool $ignore_different_length = true) : Collection
  {
    return new Collection(Arr::combine($this->list, $values));
  }

  public function concat(array $values) : Collection
  {
    $concatenated = array_merge(array_values($this->list), array_values($values));
    return new Collection($concatenated);
  }

  public function contains($value, string $key = null) : bool
  {
    return Arr::contains($this->list, $value, $key);
  }

  public function doesntContain($value, string $key = null) : bool
  {
    return !$this->contains($value, $key);
  }

  public function containsOneItem() : bool
  {
    return count($this->list) === 1;
  }

  public function count() : int
  {
    return count($this->list);
  }

  public function countBy(Closure|string $fn = null) : Collection
  {
    $counted = Arr::countBy($this->list, $fn);
    return new Collection($counted);
  }

  public function dd() : void
  {
    dd($this);
  }

  public function dump() : void
  {
    dump($this);
  }

  public function dot() : Collection
  {
    $dot_arr = Arr::toDot($this->list);
    return new Collection($dot_arr);
  }

  public function each(Closure $fn) : void
  {
    Arr::each($this->list, $fn);
  }

  public function ensure(string $type) : void
  {
    $res = Arr::ensure($this->list, $type);
    if(!$res)
      dd('Wrong array by type');
  }

  public function every(Closure $fn) : bool
  {
    return Arr::every($this->list, $fn);
  }

  public function except(array|string $except) : Collection
  {
    $res = Arr::getExcept($this->list, $except);
    return new Collection($res);
  }

  public function only(array|string $only) : Collection
  {
    $res = Arr::getOnly($this->list, $only);
    return new Collection($res);
  }

  public function filter(Closure $fn = null) : Collection
  {
    if(is_null($fn))
      $fn = function($itm){
        return $itm !== false;
      };

    $res = Arr::filter($this->list, $fn);
    return new Collection($res);
  }

  public function first(Closure $fn = null) : mixed
  {
    return Arr::first($this->list, $fn);
  }

  public function firstWhere(string $key, string $operator = null, string $value = null) : mixed
  {
    return Arr::firstWhere($this->list, $key, $operator, $value);
  }

  public function replace(array $data) : mixed
  {
    $res = Arr::replace($this->list, $data);
    return new Collection($res);
  }

  public function flatMap(Closure $fn) : Collection
  {
    $res = Arr::flatMap($this->list, $fn);
    return new Collection($res);
  }

  /*
   * Flattens a multi-dimensional collection into a single dimension
   * Depth level to flatten could be passed as a parameter
   */
  public function flatten(int $depth = null) : Collection
  {
    $res = Arr::flatten($this->list, $depth);
    return new Collection($res);
  }

  /*
   * Removes an item by its key
   * Does not return a new modified collection, modifies current collection
   */
  public function forget(array|string $keys) : Collection
  {
    $this->list = Arr::forget($this->list, $keys);
    return $this;
  }

  /*
   * Returns the item at a given key
   * $key could be a dot pattern as {key.second_key.another_key} for deep access
   * $value_on_empty could be a callback
   */
  public function get(string $key, $value_on_empty = null) : mixed
  {
    return Arr::getByDotPattern($this->list, $key, $value_on_empty);
  }

  /*
   * Groups the items by a given key
   * If callback passed as key it should return the value to group by
   */
  public function groupBy(Closure|string|array $key) : Collection
  {
    $res = Arr::groupBy($this->list, $key);
    return new Collection($res);
  }

  /*
   * Determines if a given key exists and not empty
   */
  public function has(array|string $key) : bool
  {
    return Arr::hasByDotPattern($this->list, $key);
  }

  /*
   * Determines whether any of the given keys exist and not empty
   */
  public function hasAny(array|string $key) : bool
  {
    return Arr::hasAnyDotPattern($this->list, $key);
  }

  /*
   * Determines if a given key exists
   */
  public function exists(array|string $key) : bool
  {
    return Arr::existsByDotPattern($this->list, $key);
  }

  /*
   * Determines whether any of the given keys exist
   */
  public function existsAny(array|string $key) : bool
  {
    return Arr::existsAnyByDotPattern($this->list, $key);
  }

  /*
   * Determines if empty
   */
  public function isEmpty() : bool
  {
    return empty($this->list);
  }

  /*
   * Determines if not empty
   */
  public function isNotEmpty() : bool
  {
    return !empty($this->list);
  }

  /*
   * Joins the values with a string
   */
  public function join(string $separator, string $last_separator = null) : string
  {
    return Arr::join($this->list, $separator, $last_separator);
  }

  /*
   * Keys by the given key
   */
  public function keyBy(Closure|string $key) : Collection
  {
    $res = Arr::groupBy($this->list, $key);
    return new Collection($res);
  }

  /*
   * Returns all keys
   */
  public function keys() : Collection
  {
    $res = array_keys($this->list);
    return new Collection($res);
  }

  /*
   * Returns the last element(that matches closure condition)
   * If empty data(list) null returned
   */
  public function last(Closure $fn = null) : mixed
  {
    return Arr::last($this->list, $fn);
  }

  /*
   * Iterates over the collection,
   * creating a new instance of the given class
   * by passing the value into the constructor
   */
  public function mapInto(string $сl) : Collection
  {
    if(!class_exists($сl))
      return $this->collect();

    $list = array_map(function ($itm) use ($сl) {
      return new $сl($itm);
    }, $this->list);

    return new Collection($list);
  }

  /*
   * Returns the maximum value of a given key
   */
//  public function max(string $key = null) : mixed
//  {
//    return Arr::max($this->list, $key);
//  }

}