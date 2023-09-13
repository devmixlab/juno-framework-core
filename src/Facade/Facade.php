<?php
namespace Juno\Facade;

//use Juno\Facade\Facade;
class Facade{

  public static $default_facades = [
    'App' => \Juno\App\ApplicationFacade::class,
    'Router' => \Juno\Routing\RouterFacade::class,
    'Request' => \Juno\Request\RequestFacade::class,
    'Arr' => \Juno\Helpers\Arr\ArrFacade::class,
    'Str' => \Juno\Facades\Str::class,
  ];

}