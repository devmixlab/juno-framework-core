<?php
namespace Juno\Collection;

use Closure;
use Arr;

class Collection extends InitCollection {

  /*
   * LEFT TO IMPLEMENT:
   *
   * diff, crossJoin, diffAssoc, diffAssocUsing, diffKeys,
   * flip, forPage, implode, intersect, intersectAssoc,
   * duplicates, duplicatesStrict, eachSpread, firstOrFail,
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


  /*
   * Iterates through the collection and passes each value to the given callback.
   * The callback is free to modify the item and return it,
   * thus forming a new collection of modified items
   */
  public function map(Closure $fn) : Collection
  {
    $list = Arr::map($this->list, $fn);
    return new Collection($list);
  }

  /*
   * Iterates over the collection and calls the given callback
   * with each item in the collection
   * The items in the collection will be replaced
   * by the values returned by the callback
   */
  public function transform(Closure $fn) : Collection
  {
    foreach($this->list as $k => $v)
      $this->list[$k] = $fn($v, $k);
    return $this;
  }

  /*
   * Filters the collection using the given closure
   * The closure should return true if the item should be removed
   * from the resulting collection
   */
  public function reject(Closure $fn) : Collection
  {
    $res = Arr::reject($this->list, $fn);
    return new Collection($res);
  }

  /*
   * Returns the collection`s array
   */
  public function toArray() : array
  {
    return $this->list;
  }

  /*
   * Returns the collection`s array
   */
  public function all() : array
  {
    return $this->list;
  }

  /*
   * Average value of a given key
   */
  public function avg(string $key = null) : float
  {
    return Arr::avg($this->list, $key);
  }

  /*
   * Breaks the collection into multiple,
   * smaller collections of a given size
   */
  public function chunk(int $length) : Collection
  {
    $chunks = [];

    $chunk = function(array $data, int $length) use (&$chunk, &$chunks){
      $chunk_arr = array_slice($data, 0, $length);
      $chunks[] = new Collection($chunk_arr);

      $left = array_slice($data, $length);
      if(!empty($left))
        $chunk($left, $length);
    };

    $chunk($this->list, $length);
    return new Collection($chunks);
  }

  /*
   * Collapses a collection of arrays into a single,
   * flat collection (1 level depth)
   */
  public function collapse() : Collection
  {
    return new Collection(Arr::flatten($this->list, 1));
  }

  /*
   * Returns a new Collection instance
   * with the items currently in the collection
   */
  public function collect() : Collection
  {
    return new Collection($this->list);
  }

  /*
   * Combines the values of the collection, as keys,
   * with the values of another array or collection
   */
  public function combine(Collection|array $values, bool $ignore_different_length = true) : Collection
  {
    if($values instanceof Collection)
      $values = $values->all();

    return new Collection(Arr::combine($this->list, $values));
  }

  /*
   * Appends the given array or collection's values
   * onto the end of another collection
   */
  public function concat(Collection|array $values) : Collection
  {
    if($values instanceof Collection)
      $values = $values->all();

    $concatenated = array_merge(array_values($this->list), array_values($values));
    return new Collection($concatenated);
  }

  /*
   * Determines whether the collection contains a given item
   */
  public function contains(Closure|string $key, mixed $value = null) : bool
  {
    return Arr::contains($this->list, $key, $value);
  }

  /*
   * Determines whether the collection does not contain a given item
   */
  public function doesntContain(Closure|string $key, mixed $value = null) : bool
  {
    return !$this->contains($key, $value);
  }

  /*
   * Determines whether the collection contains a single item
   */
  public function containsOneItem() : bool
  {
    return $this->count() === 1;
  }

  /*
   * Returns the total number of items in the collection
   */
  public function count() : int
  {
    return count($this->list);
  }

  /*
   * Counts the occurrences of values in the collection
   * By default counts the occurrences of every element
   * Pass a closure to count all items by a custom value
   */
  public function countBy(Closure|string $fn = null) : Collection
  {
    $counted = Arr::countBy($this->list, $fn);
    return new Collection($counted);
  }

  /*
   * Dumps the collection's items and ends execution of the script
   */
  public function dd() : void
  {
    dd($this);
  }

  /*
   * Dumps the collection's items
   */
  public function dump() : void
  {
    dump($this);
  }

  /*
   * Flattens a multi-dimensional collection into a single level collection
   * that uses "dot" notation to indicate depth
   */
  public function dot() : Collection
  {
    $dot_arr = Arr::toDot($this->list);
    return new Collection($dot_arr);
  }

  /*
   * Iterates over the items in the collection
   * and passes each item to a closure
   */
  public function each(Closure $fn) : void
  {
    Arr::each($this->list, $fn);
  }

  /*
   * Verifies that all elements of a collection are of a given type
   */
  public function ensure(string $type) : bool
  {
    return Arr::ensure($this->list, $type);
  }

  /*
   * Verifies that all elements of a collection
   * pass a given truth test
   */
  public function every(Closure $fn) : bool
  {
    return Arr::every($this->list, $fn);
  }

  /*
   * Returns all items in the collection
   * except for those with the specified keys
   */
  public function except(array|string $except) : Collection
  {
    $res = Arr::getExcept($this->list, $except);
    return new Collection($res);
  }

  /*
   * Returns only items in the collection
   * with the specified keys
   */
  public function only(array|string $only) : Collection
  {
    $res = Arr::getOnly($this->list, $only);
    return new Collection($res);
  }

  /*
   * filters the collection using the given callback,
   * keeping only those items that pass a given truth test
   */
  public function filter(Closure $fn = null) : Collection
  {
    if(is_null($fn))
      $fn = function($itm){
        return $itm !== false;
      };

    $res = Arr::filter($this->list, $fn);
    return new Collection($res);
  }

  /*
   * returns the first element in the collection that passes a given truth test
   * with no arguments will return just first element
   */
  public function first(Closure $fn = null) : mixed
  {
    return Arr::first($this->list, $fn);
  }

  /*
   * Returns the first element in the collection with the given key / value pair
   * works with two dimensional arrays
   * also can be called with a comparison operator or
   * called only with one argument
   * (will return the first item where the given item key's value is "truthy")
   */
  public function firstWhere(string $key, string $operator = null, string $value = null) : mixed
  {
    return Arr::firstWhere($this->list, $key, $operator, $value);
  }

  public function replace(array $data) : mixed
  {
    $res = Arr::replace($this->list, $data);
    return new Collection($res);
  }

  /*
   * iterates through the collection and passes each value to the given closure.
   * The closure is free to modify the item and return it,
   * thus forming a new collection of modified items.
   * Then, the array is flattened by one level
   */
  public function flatMap(Closure $fn) : Collection
  {
    $list = Arr::map($this->list, $fn);
    $list = Arr::flatten($list, 1);
    return new Collection($list);
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
  public function groupBy(Closure|string|array $key, $preserveKeys = false) : Collection
  {
    $res = Arr::groupBy($this->list, $key, preserveKeys: $preserveKeys);
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

}