<?php
namespace Juno\View;

use Juno\Exceptions\ViewException;
use Juno\View\Parsers\SlotParser;
use Juno\View\Parsers\DataAttributesParser;
use Juno\View\Parsers\TopParser;
use Closure;

class InitView{

  protected string $component_prefix = 'x-';

  protected string $view_path;

  protected string $html;

  protected Directives $directives;

  static protected array $composed = [];

  protected string $core_path = __DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR;

  public function __construct(protected string $dotted_path, protected array $params = [], bool $is_core = false)
  {
//    dd($params);
    $this->params = $this->applyComposed($params);

    $this->directives = new Directives($this);
    $path = str_replace(".",DIRECTORY_SEPARATOR, $dotted_path);
    $this->view_path = ($is_core ? $this->core_path : VIEW_PATH) . $path . '.php';

    $html = $this->loadFile($this->view_path);
    $this->html = $html;
  }

  protected function applyComposed(array $data): array {
//    dd($data);
    if(empty(self::$composed))
      return $data;

    foreach(self::$composed as $k => $v){
      if(is_string($v) && class_exists($v)){
        $v = new $v;
      }else if($v instanceof Closure){
        $v = $v();
      }

      $data[$k] = $v;
    }

    return $data;
  }

  static public function composer(string $name, $value){
//    dd($value);
    self::$composed[$name] = $value;
  }

  public function params(): array {
    return $this->params;
  }

  protected function loadFile(string $path, $component = null): string {
    if(!file_exists($path))
      throw ViewException::forWrongPath($this->dotted_path);

    $params = $this->params;
    $data = file_get_contents($path);

    $data_attributes_parser = new DataAttributesParser($data);
    $data_attributes_parser->proccessDataAttributes();
    $data = $data_attributes_parser->toHtml();

    if(!empty($component)){
      $data = $component->parseProps($data);
    }

    $data = (new TopParser($data))->html();
//    // Replaces {{ {data} }} with executable php tags
/*    $data = preg_replace('/\{\{(((?!\{\{|\}\})[\s\S])*)\}\}/i','<?= $1 ?>', $data);*/
//    // Replaces @csrf with input
//    $data = str_replace("@csrf", '<input type="hidden" name="__csrf" value="' . \Csrf::get() . '">', $data);
//    // Replaces @method('method') with input
//    $data = preg_replace("/@method\(('|\")[ ]*([A-z]+)[ ]*(\g1)\)/", '<input type="hidden" name="__method" value="$2">', $data);

    file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'temp.php', $data);

    $html = (static function () use ($params, $component) {
      extract($params);
      if(!empty($component)){
        extract($component->slots());
        $slot = $component->content();
        $props = $component->props();
        extract($props);
      }
      unset($params, $props);

      ob_start();
      require(__DIR__ . DIRECTORY_SEPARATOR . 'temp.php');
      return ob_get_clean();
    })();

    $html = trim($html);
    $html = $this->directives->stack($html);

    return $html;
  }

  protected function resolveHtml($iii = 1) {
    $html_handler = new HtmlHandler($this->html, $this->component_prefix);

//    if($res = $html_handler->isNotValid()){
//      dd($res);
//    }

    $component_parser = new ComponentParser($this->html);
    $component_parts = $component_parser->getComponentParts();

    foreach($component_parts as $component_part){
      $path = $this->makePathFromComponentTagName($component_part["tag_name"]);
      $component_class = $this->makeComponentClassFromComponentTagName($component_part["tag_name"]);

//      dump($component_class);
      $component = class_exists($component_class) ?
        new $component_class($component_part) : new Component($component_part);

      $loaded_html = $this->loadFile($path,$component ?? null);

      $component_parser->setComponentPartFull($component_part["index"], $loaded_html);
      $html = $component_parser->toHtml();

      $this->html = $html;
      $this->resolveHtml(++$iii);
    }
  }

  protected function makePathFromComponentTagName(string $tag_name): string {
    $str = ltrim($tag_name, $this->component_prefix);
    $str = str_replace('.', DIRECTORY_SEPARATOR, $str);
    return VIEW_PATH . $str . '.php';
  }

  protected function makeComponentClassFromComponentTagName(string $tag_name): string {
//    $tag_name = "x-contact_us.listt";

    $class = "\\App\\View\\Components\\";
    $str = ltrim($tag_name, $this->component_prefix);
    $str_arr = explode('.', $str);

//    dd($str_arr);

    foreach($str_arr as $k => &$v){
      $v_arr = explode('_', $v);
      $v = implode(array_map(fn($itm) => ucfirst($itm), $v_arr));
    }

    $class .= implode("\\", $str_arr);

//    if(count($str_arr) > 1){
//      $str = array_pop($str_arr);
//      $class .= implode("\\", array_map(fn($itm) => ucfirst($itm), $str_arr)) . "\\";
//    }
//
//    $str = implode('', array_map(fn($itm) => ucfirst($itm), explode('_', $str)));
//    dump($str);
//    $class .= $str;
    return $class;
  }

}