<?php
namespace Juno\View;

use DOMDocument;

class View{

  protected string $component_prefix = 'x-';

  protected string $view_path;
  protected Parser $parser;

  protected string $html;

  protected $doc;
  protected array $directives = [];

  protected $html_with_empty_body;

  protected string $core_path = __DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR;

  public function __construct(string $path, protected array $params = [], bool $is_core = false)
  {
//    throw new \Juno\Exceptions\ViewException("Wrong content");

    $path = str_replace(".",DIRECTORY_SEPARATOR, $path);
    $this->view_path = ($is_core ? $this->core_path : VIEW_PATH) . $path . '.php';


//    if(strpos($path, DIRECTORY_SEPARATOR)){
//      $this->view_path = $path;
//    }else if(!$is_core && defined('VIEWS_PATH')){
//      $this->view_path = VIEWS_PATH . str_replace(".",DIRECTORY_SEPARATOR, $path) . '.php';
//    }else if($is_core){
////      $this->view_path = __DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR;
//      $this->view_path .= str_replace(".",DIRECTORY_SEPARATOR, $path) . '.php';
//    }
  }

  public function make() : string
  {
//    libxml_use_internal_errors(true);
    $this->html = $this->loadFile($this->view_path);
//    ddh($this->html);
    $this->resolveHtml();

//    ddh($this->html);

    $this->html = $this->applyDirectives($this->html);

//    $this->html_with_empty_body

    if(!empty($this->html_with_empty_body)){
      $this->html_with_empty_body = $this->applyDirectives($this->html_with_empty_body);
      $this->html_with_empty_body = Parser::initDOMDocument($this->html_with_empty_body);
//      ddh($this->html_with_empty_body);
//      $this->html_with_empty_body
      $list = $this->html_with_empty_body->getElementsByTagName('body');
      $el_to_replace = $list->item(0);

      $el = $this->html_with_empty_body->createElement('body');
//      $el = $parser->createElementFromHtml($html);
      $el_to_replace->replaceWith($el);
      $html = $this->html_with_empty_body->saveHTML();

//      ddh($html);
//      dd($el->item(0));
      ddh($this->html);
    }

    return $this->html;
  }

  public function applyDirectives(string $html): string {
    foreach($this->directives as $k => $v){
      if($k == 'push'){
        $stack = [];
        foreach($v as $kk => $vv){
          if(!array_key_exists($vv[2], $stack)){
            $stack[$vv[2]] = [
              "name" => $vv[2],
              "content" => $vv[5],
            ];
          }else{
            $stack[$vv[2]]['content'] .= $vv[5];
          }
        }

        foreach($stack as $kk => $vv){
          $pattern = "/@stack\((\'|\"){1}(([A-z])+)(\'|\"){1}\)/";
          $html = preg_replace($pattern, $vv['content'], $html);
        }
      }
    }

    return $html;
  }

  public function makePathFromTagName(string $tag_name, string $prefix) : string
  {
    $str = ltrim($tag_name, $prefix);
    $str = str_replace('.', DIRECTORY_SEPARATOR, $str);
    return VIEW_PATH . $str . '.php';
  }

  public function loadFile(string $path, string $slot = null)
  {
    if(!file_exists($path))
      dd($path);

    $load = static function ($params) use ($path, $slot) {
      $data = file_get_contents($path);

      $data = str_replace("{{", '<?= ', $data);
      $data = str_replace("}}", ' ?>', $data);
      file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'temp.php', $data);

      extract($params);
      ob_start();

      require(__DIR__ . DIRECTORY_SEPARATOR . 'temp.php');
      return ob_get_clean();
    };

    return $load($this->params);
  }

  public function appendDirectives(array $directives)
  {
    if(empty($directives))
      return;

    foreach($directives as $k => $v){
      if(array_key_exists($k, $this->directives) && is_array($this->directives[$k])){
        $this->directives[$k] = array_merge($this->directives[$k], $v);
      }else{
        $this->directives[$k] = $v;
      }
    }
  }

  public function resolveHtml()
  {
//    dd($this->html);
    $parser = new Parser($this->html, $this->component_prefix);
    if(preg_match('/^<html>[\s\S]*<\/html>$/', trim($this->html))){
      $this->html_with_empty_body = $this->html;
    }

    if($res = $parser->isBodyNotValid()){
      dd($res);
    }

//    $directives = $parser->getDirectives();
//    $this->appendDirectives($directives);

//    dd($this->directives);

//    dump($this->html);

    $nodes = $parser->getTopComponentsNodes();
    foreach($nodes as $node){
      $path = $this->makePathFromTagName($node->tagName, $parser->getComponentPrefix());

      $html = $parser->saveHTML($node);
//      ddh($html);

//      $directives = $parser->getDirectives();
//      $this->appendDirectives($directives);


      $html = $this->loadFile($path, $html);

      ["directives" => $directives, "html" => $html] = $parser->getDirectives($html);
      $this->appendDirectives($directives);

//      ddh($html);

//      $directives = $parser->getDirectives();
//      $this->appendDirectives($directives);

//      $parser->getDirectives($html);
//      ddh($html);

      if(preg_match('/<html>[\s\S]*<\/html>/', $html)){
        $this->html = $html;
      }else{
        $el = $parser->createElementFromHtml($html);
        $node->replaceWith($el);
        $this->html = $parser->saveHTML();
      }

//      dump($this->html);

//      $directives = $parser->getDirectives();
//      $this->appendDirectives($directives);

      $this->resolveHtml();
    }
  }

//  public function make() : string
//  {
////    libxml_use_internal_errors(true);
//    $this->html = $this->loadComponentFile($this->view_path);
//    $this->resolveContent();
//
//    return $this->html;
//  }

}