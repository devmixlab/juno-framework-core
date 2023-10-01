<?php
namespace Juno\View;

//use DOMDocument;
//use DOMXPath;
//use DOMElement;
//use Juno\Exceptions\AppException;
//use Juno\Exceptions\ViewException;

class ComponentParser{

  protected $open_tag = "/<((x-([A-z0-9\._-]*))( )*(\/)?)>/";

  protected $close_tag = "/<((x-([A-z0-9\._-]*))( )*(\/)?)>/";

  protected $parts = [];

  public function __construct(string $html){
    $this->html = HtmlHandler::removeHtmlComments($html);
//    ddh($html);
    $this->parse($html);
  }

  public function parts(): array {
    return $this->parts;
  }

  protected function parse(string $html): void {
    $parse = function($html) use (&$parse) {
      $res = preg_match($this->open_tag, $html, $matches);

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

      $tag_name = $matches[2];
      $open_tag = "<" . $matches[1] . ">";
      $open_pos = strpos($html, $open_tag);
      $left_html = substr($html, $open_pos + strlen($open_tag));

      if(!empty($matches[5])){
        $this->parts[] = [
          "index" => count($this->parts),
          "type" => "text",
          "full" => substr($html, 0, $open_pos),
        ];

        $this->parts[] = [
          "index" => count($this->parts),
          "type" => "component",
          "tag_name" => $tag_name,
          "open_tag" => $open_tag,
          "is_self_closed" => true,
          "full" => $open_tag,
        ];

        $parse($left_html);
        return;
      }

      if($open_pos > 0){
        $this->parts[] = [
          "index" => count($this->parts),
          "type" => "text",
          "full" => substr($html, 0, $open_pos),
        ];
      }

      $res = preg_match("/<((\/{$tag_name})( )*)>/", $left_html, $matches);
      if($res == 0){
        $this->parts[] = [
          "index" => count($this->parts),
          "type" => "text",
          "full" => $open_tag,
        ];
        $left_html = substr($left_html, $open_pos + strlen($open_tag));
        $parse($left_html);
        return;
      }

      $close_tag = "<" . $matches[1] . ">";
      $close_pos = strpos($left_html, $close_tag);

      $content = substr($left_html, 0, $close_pos);
      $full = $open_tag . $content . $close_tag;

      $this->parts[] = [
        "index" => count($this->parts),
        "type" => "component",
        "tag_name" => $tag_name,
        "open_tag" => $open_tag,
        "close_tag" => $close_tag,
        "full" => $full,
        "content" => $content,
      ];

      $left_html = substr($left_html, $close_pos + strlen($close_tag));

//      if($tag_name == 'x-test')
//        ddh($left_html);

      $parse($left_html);
    };

    $parse($html);

//    $this->parts = $parts;

//    dd($this->parts);

//    return $components;
  }

  public function toHtml(): string {
    $str = "";
    foreach($this->parts as $k => $v){
      $str .= $v['full'];
    }
    return $str;
  }

  public function setComponentFull(int $idx, string $html): void {
    $this->parts[$idx]['full'] = $html;
  }

  public function getComponents(): array {
    return array_filter($this->parts, function($itm){
      return !empty($itm['type']) && $itm['type'] == 'component';
    });
  }

  public function getTopComponents(): array {

    $components = [];

    $parse = function($html) use (&$components, &$parse) {
      $res = preg_match($this->open_tag, $html, $matches);

      if($res == 0)
        return;

      $tag_name = $matches[2];
//      dd($matches);
      $open_tag = "<" . $matches[1] . ">";
      $open_pos = strpos($html, $open_tag);
      $left_html = substr($html, $open_pos + strlen($open_tag));

//      dd($new_html);

      $res = preg_match("/<((\/{$tag_name})( )*)>/", $html, $matches);
      if($res == 0){
        $parse($left_html);
      }
      $close_tag = "<" . $matches[1] . ">";
      $close_pos = strpos($html, $close_tag);
//      dd($close_pos);
//      $component_html = substr($html, strlen($str));
//      $close_pos +
//      ddh($open_tag);

      $components[] = [
        "tag_name" => $tag_name,
        "open_tag" => $open_tag,
        "close_tag" => $close_tag,
        "full" => trim(substr($html, $open_pos, $close_pos + strlen($close_tag))),
        "content" => trim(substr($html, $open_pos + strlen($open_tag), $close_pos - strlen($close_tag) + 1)),
      ];

      $left_html = trim(substr($html, $close_pos + strlen($close_tag)));

      $parse($left_html);
    };

    $parse($this->html);

    return $components;
  }

}