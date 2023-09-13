<?php
namespace Juno\Routing\Enums;

//use Juno\Router\Enums\CallerTypes;
enum CallerTypes
{
  case AS_CLOSURE;
  case AS_ARRAY;
  case AS_STRING;
}