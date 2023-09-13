<?php
namespace Juno\Routing\Enums;

enum Method : string
{
  case GET = 'get';
  case HEAD = 'head';
  case POST = 'post';
  case PUT = 'put';
  case PATCH = 'patch';
  case DELETE = 'delete';
  case OPTIONS = 'options';

  static public function isAllValuesEnums(array $data) : bool
  {
    foreach($data as $v){
      if(empty(self::tryFrom($v)))
        return false;
    }

    return true;
  }

  static public function isAnyValuesEnums(array $data) : bool
  {
    foreach($data as $v){
      if(!empty(self::tryFrom($v)))
        return true;
    }

    return false;
  }
}