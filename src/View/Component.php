<?php
namespace Juno\View;

use Juno\View\Parsers\SlotParser;
use Juno\View\Parsers\PropParser;

class Component{

  protected $tag_name;
  protected $open_tag;
  protected $close_tag;
  protected $is_self_closed;
  protected $full;
  protected string $content = '';
  protected $attributes;

  protected SlotParser $slot_parser;

  public function __construct(array $data){
    foreach([
      'tag_name','open_tag','close_tag','is_self_closed','full','content','attributes',
    ] as $key)
      if(!empty($data[$key]))
        $this->{$key} = $data[$key];

    if(!empty($this->attributes))
      $this->attributes = current((array) new \SimpleXMLElement("<element {$this->attributes} />"));

    if(!empty($this->content)){
      $this->slot_parser = new SlotParser($this->content);
      $this->content = $this->slot_parser->toHtml(function($itm){
        return !empty($itm['type']) && $itm['type'] != 'slot';
      });
    }
  }

  public function parseProps(string $content): string {
    $this->prop_parser = new PropParser($content);
    return $this->prop_parser->toHtmlWithoutProps();
//    return !empty($this->slot_parser) ?
//      $this->slot_parser->slotsNameValuePairs() : [];
  }

  public function slots(): array {
    return !empty($this->slot_parser) ?
      $this->slot_parser->slotsNameValuePairs() : [];
  }

  public function attributes(): array {
    return !empty($this->attributes) ? $this->attributes : [];
  }

  public function props(): array {
    $out = [];
    if(empty($this->prop_parser) || $this->prop_parser->isPropsEmpty())
      return [];

    $props = $this->prop_parser->props();

    foreach($props as $k => $v){
      $key = is_numeric($k) ? $v : $k;

      if(is_array($this->attributes)){
        if(array_key_exists($key, $this->attributes)){
          $out[$key] = $this->attributes[$key];
        }else if(array_key_exists(":" . $key, $this->attributes)){
          $key = ":" . $key;
          $out[$key] = $this->attributes[$key];
        }
      }else{
        if(!is_numeric($k)){
          $out[$k] = $v;
        }
      }

      unset($props[$k]);
    }

    return $out;
  }

  public function content(): string {
    return $this->content;
  }

}