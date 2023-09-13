<?php
namespace Juno\Database\QueryBuilder\Enums;

enum Join
{
case INNER;
case LEFT;
case RIGHT;
case CROSS;

  public function sqlStr() : string
{
  return match($this)
  {
    self::INNER => 'INNER JOIN',
    self::LEFT => 'LEFT JOIN',
    self::RIGHT => 'RIGHT JOIN',
    self::CROSS => 'CROSS JOIN',
  };
}

}