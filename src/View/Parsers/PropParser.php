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

class PropParser{

  protected $pattern = "/@props\(( |\n)*(\[((?!\]|\[)[\s\S])*\])( |\n)*\)/";

//  protected $close_tag = "/<((\/x-slot)( )*)>/";

  protected array $parts = [];

  protected array $props = [];

  public function __construct(string $html){
    $html = HtmlHandler::removeHtmlComments($html);
//    ddh($html);
    $this->parse($html);
//    $html = $this->toHtml(function($itm){
//      return !empty($itm['type']) && $itm['type'] != 'props';
//    });
//    $html = $this->toHtml();

//    ddh($html);
  }

  public function parts(): array {
    return $this->parts;
  }

  protected function parse(string $html): void {
    $parse = function($html) use (&$parse) {
      $res = preg_match($this->pattern, $html, $matches);

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

      $tag = $matches[0];
      $props_str = $matches[2];
      $tag_pos = strpos($html, $tag);
      $left_html = substr($html, $tag_pos + strlen($tag));

      if($tag_pos > 0){
        $this->parts[] = [
          "index" => count($this->parts),
          "type" => "text",
          "full" => substr($html, 0, $tag_pos),
        ];
      }

      $props_arr = !empty($props_str) ? eval("return " . $props_str . ";") : [];
      if(!empty($props_arr))
        $this->props = array_merge($this->props, $props_arr);

      $this->parts[] = [
        "index" => count($this->parts),
        "type" => "props",
        "tag" => $tag,
        "props_str" => $props_str,
        "props_arr" => $props_arr,
        "full" => $tag,
      ];

      $parse($left_html);
    };

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

  public function toHtmlWithoutProps(): string {
    return $this->toHtml(function($itm){
      return !empty($itm['type']) && $itm['type'] != 'props';
    });
  }

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