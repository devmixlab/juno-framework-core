<?php
namespace Juno\Request\Data;

use Arr;

class Get extends Data
{

  public function __construct()
  {
    if(!empty($_GET) && is_array($_GET)){
      $this->data = $_GET;
    }else{
      $query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
      if(!empty($query))
        parse_str($query, $this->data);
    }
  }

}