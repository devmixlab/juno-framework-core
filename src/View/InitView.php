<?php
namespace Juno\View;

use Juno\Exceptions\ViewException;

class InitView{

  protected string $component_prefix = 'x-';

  protected string $view_path;

  protected string $html;

  protected Directives $directives;

  protected $html_outer_of_body;

  protected string $core_path = __DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR;

  public function __construct(protected string $dotted_path, protected array $params = [], bool $is_core = false)
  {
    $this->directives = new Directives($this);
    $path = str_replace(".",DIRECTORY_SEPARATOR, $dotted_path);
    $this->view_path = ($is_core ? $this->core_path : VIEW_PATH) . $path . '.php';
  }

  public function params(): array {
    return $this->params;
  }

  protected function loadFile(string $path, string $slot = null)
  {
    if(!file_exists($path))
      throw ViewException::forWrongPath($this->dotted_path);

    return (static function ($params) use ($path, $slot) {
      $data = file_get_contents($path);
      extract($params);
      unset($path, $params);

      $data = preg_replace('/\{\{(((?!\{\{|\}\})[\s\S])*)\}\}/i','<?= $1 ?>', $data);

      file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'temp.php', $data);

      ob_start();
      require(__DIR__ . DIRECTORY_SEPARATOR . 'temp.php');
      return ob_get_clean();
    })($this->params);
  }

  protected function makeFullHtml() {
    if(!empty($this->html_outer_of_body)){
//      $this->html_outer_of_body = $this->applyDirectives($this->html_outer_of_body);
      $this->html_outer_of_body = $this->directives->apply($this->html_outer_of_body);
      $this->html_outer_of_body = Parser::initDOMDocument($this->html_outer_of_body);
//      ddh($this->html_with_empty_body);
//      $this->html_with_empty_body
      $list = $this->html_outer_of_body->getElementsByTagName('body');
      $el_to_replace = $list->item(0);

      $el = $this->html_outer_of_body->createElement('body');

      $html_dom = Parser::initDOMDocument($this->html);
      $el->appendChild($this->html_outer_of_body->importNode($html_dom->documentElement, TRUE));

      $el_to_replace->replaceWith($el);
      $this->html = $this->html_outer_of_body->saveHTML();
    }
  }

  protected function resolveHtml() {
    if(preg_match('/^<html>[\s\S]*<\/html>$/', trim($this->html))){
      $this->html_outer_of_body = $this->html;
    }

    $html_handler = new HtmlHandler($this->html, $this->component_prefix);

    if($res = $html_handler->isBodyNotValid()){
      dd($res);
    }

    $nodes = $html_handler->getTopComponentsNodes();
    foreach($nodes as $node){
      $path = $this->makePathFromComponentTagName($node->tagName, $this->component_prefix);

      $html = $html_handler->bodyNodeSaveHTML($node, true);

      $html = $this->loadFile($path, $html);
      $html = trim($html);

      $html = $this->directives->stack($html);

      if(!preg_match('/^<html>[\s\S]*<\/html>$/', $html)) {
        $el = $html_handler->createElementFromHtml($html);
        $node->replaceWith($el);
        $html = $html_handler->bodyNodeSaveHTML();
      }

      $this->html = $html;

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