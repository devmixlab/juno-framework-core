<?php
namespace Juno\View;

use Juno\Exceptions\ViewException;
use Juno\View\Parsers\SlotParser;

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
//    $html = $this->directives->stack($html);
    $this->html = $html;
  }

  public function params(): array {
    return $this->params;
  }

  protected function loadFile(string $path, string $slot = null, array $slots = [])
  {
    if(!file_exists($path))
      throw ViewException::forWrongPath($this->dotted_path);

    $params = $this->params;
    $data = file_get_contents($path);
    $data = preg_replace('/\{\{(((?!\{\{|\}\})[\s\S])*)\}\}/i','<?= $1 ?>', $data);
    file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'temp.php', $data);

    $html = (static function () use ($slot, $params, $slots) {
      extract($params);
      extract($slots);
      unset($params, $slots);

      ob_start();
      require(__DIR__ . DIRECTORY_SEPARATOR . 'temp.php');
      return ob_get_clean();
    })();

    $html = trim($html);

//    if(
//      preg_match('/^<html>[\s\S]*<\/html>$/', $html) &&
//      preg_match('/<body>([\s\S]*)<\/body>/', $html, $matches)
//    ) {
//      $this->layout = $html;
//      $this->layout = preg_replace('/<body>[\s\S]*<\/body>/', '<body></body>', $html);
//      $html = $matches[1];
//    }

    $html = $this->directives->stack($html);

    return trim($html);
  }

  protected function applyLayout(): void {
    if(empty($this->layout))
      return;

    $this->html = preg_replace('/<body>[\s\S]*<\/body>/', '<body>' . $this->html . '</body>', $this->layout);
  }

  protected function resolveHtml() {
    $html_handler = new HtmlHandler($this->html, $this->component_prefix);

//    if($res = $html_handler->isNotValid()){
//      dd($res);
//    }

    $component_parser = new ComponentParser($this->html);

    $components = $component_parser->getComponents();
    foreach($components as $component){
      $path = $this->makePathFromComponentTagName($component["tag_name"], $this->component_prefix);

      $html = $component["content"] ?? '';
      if(!empty($html)){
        $slot_parser = new SlotParser($html);
        $slots = $slot_parser->slotsNameValuePairs();
        $html = $slot_parser->toHtml(function($itm){
          return !empty($itm['type']) && $itm['type'] != 'slot';
        });
      }

//      ddh($component["content"]);
//      dd($slots);

      $loaded_html = $this->loadFile($path, $html, $slots ?? []);
      $component_parser->setComponentFull($component["index"], $loaded_html);
      $html = $component_parser->toHtml();

      $this->html = $html;

//      ddh($this->html);

      $this->resolveHtml();
    }
  }

  protected function makePathFromComponentTagName(string $tag_name, string $prefix) : string
  {
    $str = ltrim($tag_name, $prefix);
    $str = str_replace('.', DIRECTORY_SEPARATOR, $str);
    return VIEW_PATH . $str . '.php';
  }

}