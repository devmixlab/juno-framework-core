<?php
namespace Juno\View;

use Juno\View\Parsers\DataAttributesParser;
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

    $this->parseAttributes();

    if(!empty($this->content)){
      $this->slot_parser = new SlotParser($this->content);
      $this->content = $this->slot_parser->toHtml(function($itm){
        return !empty($itm['type']) && $itm['type'] != 'slot';
      });
    }
  }

//  public function parseDataAttributes(string $content): string {
//    $this->prop_parser = new DataAttributesParser($content);
//    return $this->prop_parser->toHtmlWithoutProps();
////    return !empty($this->slot_parser) ?
////      $this->slot_parser->slotsNameValuePairs() : [];
//  }

  public function parseAttributes(): void {
    if(empty($this->attributes) || !is_string($this->attributes))
      return;

//    dd($this->attributes);

    $data_attributes_parser = new DataAttributesParser($this->attributes, true);
    $data = $data_attributes_parser->getAsNameValuePairs();
//    $data = $data_attributes_parser->toHtml();
////    dd(111);
//    dd($data);
    $this->attributes = $data;
//    dd($data_attributes_parser->parts());

//    dd($this->attributes);
//    $this->attributes = current((array) new \SimpleXMLElement("<element {$this->attributes} />"));
  }

  public function makeProps(string $content): string {
    $this->prop_parser = new PropParser($content);
//    dump($this->prop_parser->props());
//    ddh($content);
    return $this->prop_parser->toHtmlWithoutProps();

//    ddh($content);
//    return !empty($this->slot_parser) ?
//      $this->slot_parser->slotsNameValuePairs() : [];
  }

  public function parseProps(string $content): string {
    $this->prop_parser = new PropParser($content);
//    dump($this->prop_parser->props());
//    ddh($content);
    return $this->prop_parser->toHtmlWithoutProps();

//    ddh($content);
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

//    dd(111);
//    dd($this->attributes);

    foreach($props as $k => $v){
      $key = is_numeric($k) ? $v : $k;

      if(is_array($this->attributes)){
        if(array_key_exists($key, $this->attributes)){
          $out[$key] = $this->attributes[$key];
        }else if(array_key_exists(":" . $key, $this->attributes)){
          $key = ":" . $key;
          $out[$key] = $this->attributes[$key];
        }else{
          if(!is_numeric($k)){
            $out[$k] = $v;
          }
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

  public function tagName(): string {
    return $this->tag_name;
  }

}