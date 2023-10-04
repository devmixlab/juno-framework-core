<?php
namespace Juno\View;

use Juno\Exceptions\ViewException;
use Juno\View\Parsers\SlotParser;
use Juno\View\Parsers\DataAttributesParser;

class InitView{

  protected string $component_prefix = 'x-';

  protected string $view_path;

  protected string $html;

  protected string $layout;

  protected Directives $directives;

  protected string $core_path = __DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR;

  public function __construct(protected string $dotted_path, protected array $params = [], bool $is_core = false)
  {
    $this->directives = new Directives($this);
    $path = str_replace(".",DIRECTORY_SEPARATOR, $dotted_path);
    $this->view_path = ($is_core ? $this->core_path : VIEW_PATH) . $path . '.php';

    $html = $this->loadFile($this->view_path);
//    ddh($html);
//    $html = $this->directives->stack($html);
    $this->html = $html;
  }

  public function params(): array {
    return $this->params;
  }

//  protected function loadFile(string $path, string $slot = null, array $slots = [], $component = null)
  protected function loadFile(string $path, $component = null)
  {
//    dd($component->content);

    if(!file_exists($path))
      throw ViewException::forWrongPath($this->dotted_path);

    $params = $this->params;
    $data = file_get_contents($path);

    if(!empty($component)){
//      dd(123123);
//      ddh($data);
    }

    $data_attributes_parser = new DataAttributesParser($data);
    $data_attributes_parser->proccessDataAttributes();
    $data = $data_attributes_parser->toHtml();


//    ddh($data);

    if(!empty($component)){
//      ddh($data);
      $data = $component->parseProps($data);
//      dd(11);
//      ddh($data);
    }

    $data = preg_replace('/\{\{(((?!\{\{|\}\})[\s\S])*)\}\}/i','<?= $1 ?>', $data);
    file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'temp.php', $data);

//    ddh($data);

//    $html = (static function () use ($slot, $params, $slots, $component) {
    $html = (static function () use ($params, $component) {
      extract($params);
      if(!empty($component)){
        extract($component->slots());
        $slot = $component->content();
        $props = $component->props();
        extract($props);
//        dd($props);

//        if(!empty($props))
//          dd($props);
//        $attributes = $component->attributes();
//        if(!empty($attributes))
//          dd(get_defined_vars());
//          array_map(function($itm){
//
//          }, $attributes);
//          dd($component->attributes());
      }
      unset($params, $props);

      ob_start();
      require(__DIR__ . DIRECTORY_SEPARATOR . 'temp.php');
      return ob_get_clean();
    })();

//    $data_attributes_parser = new DataAttributesParser($html);
//    if(!empty($component) && $component->tagName() == 'x-test_text'){
////      dd($component->tagName());
//      ddh($html);
//    }

//    ddh($html);

    $html = trim($html);

//    ddh($html);

//    if(
//      preg_match('/^<html>[\s\S]*<\/html>$/', $html) &&
//      preg_match('/<body>([\s\S]*)<\/body>/', $html, $matches)
//    ) {
//      $this->layout = $html;
//      $this->layout = preg_replace('/<body>[\s\S]*<\/body>/', '<body></body>', $html);
//      $html = $matches[1];
//    }

    $html = $this->directives->stack($html);

//    ddh($html);

    return trim($html);
  }

//  protected function applyLayout(): void {
//    if(empty($this->layout))
//      return;
//
//    $this->html = preg_replace('/<body>[\s\S]*<\/body>/', '<body>' . $this->html . '</body>', $this->layout);
//  }

  protected function resolveHtml($iii = 1) {
    $html_handler = new HtmlHandler($this->html, $this->component_prefix);

//    if($res = $html_handler->isNotValid()){
//      dd($res);
//    }

//    dd(11);
    $component_parser = new ComponentParser($this->html);

    $component_parts = $component_parser->getComponentParts();
//    if($iii == 2){
//      dd($component_parser->parts());
//      ddh($this->html);
//    }
    foreach($component_parts as $component_part){
      $path = $this->makePathFromComponentTagName($component_part["tag_name"]);
      $component_class = $this->makeComponentClassFromComponentTagName($component_part["tag_name"]);

//      if(class_exists($component_class))
//        dd($component_part['attributes']);

//      dd(11);
      $component = class_exists($component_class) ?
        new $component_class($component_part) : new Component($component_part);

//      if(class_exists($component_class))
//        dd(1212);

      $loaded_html = $this->loadFile($path,$component ?? null);
      $component_parser->setComponentPartFull($component_part["index"], $loaded_html);
      $html = $component_parser->toHtml();

      $this->html = $html;
//      ddh($this->html);

      $this->resolveHtml(++$iii);
    }
  }

  protected function makePathFromComponentTagName(string $tag_name): string {
    $str = ltrim($tag_name, $this->component_prefix);
    $str = str_replace('.', DIRECTORY_SEPARATOR, $str);
    return VIEW_PATH . $str . '.php';
  }

  protected function makeComponentClassFromComponentTagName(string $tag_name): string {
    $class = "\\App\\View\\Components\\";
    $str = ltrim($tag_name, $this->component_prefix);
    $str_arr = explode('.', $str);
    if(count($str_arr) > 1){
      $str = array_pop($str_arr);
      $class .= implode("\\", array_map(fn($itm) => ucfirst($itm), $str_arr)) . "\\";
    }

    $str = implode('', array_map(fn($itm) => ucfirst($itm), explode('_', $str)));
    $class .= $str;
    return $class;
  }

}