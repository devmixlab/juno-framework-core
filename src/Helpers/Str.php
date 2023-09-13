<?php
namespace Juno\Helpers;

class Str{

  protected array $characters = [
    "numbers" => '0123456789',
    "lower_letters" => 'abcdefghijklmnopqrstuvwxyz',
    "upper_letters" => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
    "specials" => '#$%^&*()_+|-=!',
  ];

  public function rand(int $length = 10, array $symbols = ['numbers','lower_letters','upper_letters','specials']) : string
  {
    $characters = '';
    if(empty($symbols)){
      $characters = implode('', $this->characters);
    }else{
      foreach($symbols as $symbol){
        if(in_array($symbol, array_keys($this->characters)))
          $characters .= $this->characters[$symbol];
      }
    }

    $characters_length = strlen($characters);
    $random_string = '';
    for ($i = 0; $i < $length; $i++) {
      $random_string .= $characters[rand(0, $characters_length - 1)];
    }

    return $random_string;
  }

  public function spaceIfNotEmpty($data) : string
  {
    return !empty($data) ? ' ' : '';
  }

  public function isEqualByStarPattern(string $value, string $pattern) : string
  {
    $pattern = str_replace("*", "(.*)", $pattern);
    $pattern = str_replace('/', '\/', $pattern);
    return preg_match("/^" . $pattern . "$/i", $value);
  }

}