<?php
namespace Juno\View\Parsers;

use Juno\View\ComponentParser;
use Juno\View\HtmlHandler;
use Closure;

//use DOMDocument;
//use DOMXPath;
//use DOMElement;
//use Juno\Exceptions\AppException;
//use Juno\Exceptions\ViewException;

class DataAttributesParser{

//  protected $pattern = "/((:)?([A-z]+))=((\"([A-z0-9_\-.,$! ]+)\")|(\'([A-z0-9_\-.,$! ]+)\'))/";
  protected $pattern = "/((:)?([A-z0-9]+))=((\"|\')(([()A-z0-9_\-.,$! '\"\n]+)|({##)(((?!{##|##})[\s\S])+)(##}))\g5)/";
//  protected $pattern = "/((:)?([A-z0-9]+))=((\"|\')([A-z0-9_\-.,\$! ]+)\g5)/";

  protected array $parts = [];

//  protected array $props = [];

  public function __construct(string $html, protected bool $test = false){
    $html = HtmlHandler::removeHtmlComments($html);
//    ddh($html);
    $this->parse($html);



//    ddh($this->toHtml());
//    dd($this->parts);
//    $html = $this->toHtml(function($itm){
//      return !empty($itm['type']) && $itm['type'] != 'props';
//    });
//    $html = $this->toHtml();

//    ddh($html);
  }

  public function proccessDataAttributes(): void {
    foreach($this->parts as &$part){
      if($part["type"] != "attr" || $part["is_data"] !== true ||
        $part["attr_type"] == "data_parsed")
        continue;

//      if(!empty($part["value"]) && )


//      if($this->test)
//        dd($part);

//      if()
//      $part_value = serialize($part['value']);
      $part["full"] = str_replace($part['value'], "{##{{ serialize({$part['value']}) }}##}", $part["full"]);

//      dd($part);
//      $pattern = ":{$}";
//      preg_replace();

    }
//    dd(11);
  }

  public function getAsNameValuePairs(): array {
    $out = [];
    foreach($this->parts as $part){
      if($part['type'] != 'attr')
        continue;

      $part_value = $part["attr_type"] == 'data_parsed' ?
        preg_replace("{{##([\s\S]*)##}}", '$1', $part["value"]) : $part["value"];

//      if($part["attr_type"] == 'data_parsed') {
//        dd(unserialize($part_value));
//      }
//      if($part["attr_type"] == 'data_parsed'){
//        dump($part["value"]);
////        dd(rtrim(ltrim($part["value"], "{##"), "##}"));
////        preg_match("{##([\s\S]*)##}", $part["value"], $mm);
////        dd($mm);
//        dd(preg_replace("{{##([\s\S]*)##}}", '$1', $part["value"]));
//      }
//        dd(unserialize($part_value . "}"));

      $key = ltrim($part['name'], ':');
      $out[$key] = match($part["attr_type"]){
        'static','data' => $part["value"],
//        'data_parsed' => eval("return " . unserialize($part_value) . ";"),
        'data_parsed' => unserialize($part_value),
      };
    }

    return $out;
  }

  public function parts(): array {
    return $this->parts;
  }

  protected function parse(string $html): void {
    $parse = function($html) use (&$parse) {
//      dd(4343);
      $res = preg_match($this->pattern, $html, $matches);

//      dd($this->pattern);
//      dump($res);
//      ddh($html);
//      dd($matches);

      if($res == 0){
        if(!empty($html))
          $this->parts[] = [
            "index" => count($this->parts),
            "type" => "text",
            "full" => $html,
          ];
        return;
      }

//      if($this->test)
//        dump($matches);

      $attr = $matches[0];
      $name = $matches[1];
      $is_data = str_starts_with($name, ":");
      $value = $matches[4];
      $attr_pos = strpos($html, $attr);
      $left_html = substr($html, $attr_pos + strlen($attr));

      if(str_starts_with($name, ":")){
        if(preg_match("/^{##[\s\S]+##}$/", $matches[6]) > 0){
          $attr_type = "data_parsed";
        }else{
          $attr_type = "data";
        }
      }else{
        $attr_type = "static";
      }


//      if($this->test && $is_data)
//        dd($matches);

      if($attr_pos > 0){
        $this->parts[] = [
          "index" => count($this->parts),
          "type" => "text",
          "full" => substr($html, 0, $attr_pos),
        ];
      }

//      $props_arr = !empty($props_str) ? eval("return " . $props_str . ";") : [];
//      if(!empty($props_arr))
//        $this->props = array_merge($this->props, $props_arr);

      $this->parts[] = [
        "index" => count($this->parts),
        "type" => "attr",
        "attr" => $attr,
        "name" => $name,
//        "value" => $value,
        "value" => substr($value, 1, -1),
        "full" => $attr,
        "attr_type" => $attr_type,
        "is_data" => $is_data,
      ];

      $parse($left_html);
    };

//    dd(222);

    $parse($html);
  }

  public function toHtml(Closure $fn = null): string {
    $str = "";
    foreach($this->parts as $k => $v){
      if($fn !== null){
        if($fn($v))
          $str .= $v['full'];
      }else{
        $str .= $v['full'];
      }
    }
    return $str;
  }

//  public function toHtmlWithoutProps(): string {
//    return $this->toHtml(function($itm){
//      return !empty($itm['type']) && $itm['type'] != 'props';
//    });
//  }

  public function getSlots(): array {
    return array_filter($this->parts, function($itm){
      return !empty($itm['type']) && $itm['type'] == 'slot';
    });
  }

  public function props(): array {
    return $this->props;
  }

  public function isPropsEmpty(): bool {
    return empty($this->props);
  }

}