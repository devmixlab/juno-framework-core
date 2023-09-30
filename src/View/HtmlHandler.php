<?php
namespace Juno\View;

use DOMDocument;
use DOMXPath;
use DOMElement;

class HtmlHandler{

  protected $dom;
  protected $xp;
  protected $html;

  static protected $singleton_tags = [
    "area","base","br","col","command","embed","hr","img","input","keygen",
    "link","meta","param","source","track","wbr"
  ];

  public function __construct(
    string $html,
    protected string $component_prefix,
    bool $remove_comments = true
  )
  {
    $this->html = trim($html);
    $this->dom = $this->initDOMDocument($html);
    $this->xp = new DOMXPath($this->dom);

    if($remove_comments){
      // Remove comments from BODY_DOM
      foreach ($this->xp->query('//comment()') as $comment) {
        $comment->parentNode->removeChild($comment);
      }
    }
  }

  public function isNotValid(): mixed {
//    ddh($this->html);
    return static::isHtmlNotValid($this->html);
  }

  public function getTopComponentsNodes(): array {
    $component_tags = [];
    $cp = $this->component_prefix;

    foreach ( $this->xp->query( "//*[starts-with(local-name(),'{$cp}')][not(ancestor::*[starts-with(local-name(),'{$cp}')])]" ) as $node ) {
      if(!in_array($node->tagName, $component_tags)){
        $component_tags[] = $node->tagName;
      }
    }

    $nodes = [];
    foreach($component_tags as $tag){
      $node_list = $this->dom->getElementsByTagName($tag);
      if($node_list->length > 0)
        foreach($node_list as $node){
          if(!$this->isNodeHasParentComponent($node))
            $nodes[] = $node;
        }
    }

    return $nodes;
  }

  public function createElementFromHtml(string $html){
    $d = Parser::initDOMDocument($html);
    $el = $this->dom->createElement('div');
    $el->appendChild($this->dom->importNode($d->documentElement, TRUE));
    return $el;
  }

  protected function isNodeHasParentComponent(DOMElement $node): bool {
    if(empty($node->parentNode))
      return false;

    do{
      $node = $node->parentNode;
      if(empty($node) || empty($node->tagName))
        break;

      $tag = $node->tagName;

      if(str_starts_with($tag, $this->component_prefix))
        return true;
    }while(true);

    return false;
  }

  static public function removeHtmlComments(string $html): string {
    return preg_replace("/<!--((?!-->|<!--)[\s\S])*?-->/", "", $html);
  }

  static public function isHtmlNotValid(string $html): mixed {
    $html = static::removeHtmlComments($html);

    // Take only body
    if(preg_match('/<body>[\s\S]*<\/body>/', $html, $matches)){
      $html = $matches[0];
//      $html = preg_replace('/(<body>)|(<\/body>)/', '', $html);
    }

    $html = trim($html);

    libxml_clear_errors();
    simplexml_load_string('<div>' . $html . '</div>');
    $libxml_errors = libxml_get_errors();
    libxml_clear_errors();

    if(count($libxml_errors) > 0){
      $err = $libxml_errors[0];
      $err = explode('line', $err->message)[0];
      $err = trim($err);

      $pos = stripos($err, "opening and ending tag mismatch");
      if(is_numeric($pos)){
        $err_arr = explode(':', $err);
        $tag = trim($err_arr[1]);
        if(in_array($tag, static::$singleton_tags)){
          $html = preg_replace("/<{$tag}( )*(\/)?>/",'', $html);
          return static::isHtmlNotValid($html);
        }
      }

      return $err;
    }

    return false;
  }

  static public function initDOMDocument(string $html): DOMDocument {
    $dom = new DOMDocument();
    $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);
    return $dom;
  }

  public function bodyNodeSaveHTML(DOMElement $node = null, bool $strip_top_tag = false): string {
    if($strip_top_tag)
      return implode(
        array_map([$node->ownerDocument, "saveHTML"], iterator_to_array($node->childNodes))
      );

    return !empty($node) ? $this->dom->saveHTML($node) : $this->dom->saveHTML();
  }

}