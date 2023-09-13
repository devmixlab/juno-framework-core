<?php
namespace Juno\Request\Data;

use Arr;

class Input extends Data
{

  public function __construct()
  {
    if(!empty($_POST) && is_array($_POST)){
      $this->data = $_POST;
    }else{
      $input  = "php://input";
      $input_json = file_get_contents($input);
      $data = json_decode($input_json, true);

      if(!empty($data) && is_array($data))
        $this->data = $data;
    }
  }

}