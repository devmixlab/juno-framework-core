<?php
namespace Juno\View;

use DOMDocument;
use DOMXPath;
use DOMElement;
use Juno\Exceptions\AppException;
use Juno\Exceptions\ViewException;

class Parser{

//  protected string $component_prefix = 'x-';
  public DOMDocument $dom;
  protected $xp;
//  protected $root;
//  protected $xml;
  protected string $body;
  protected $html_with_empty_body;
  protected mixed $id_body_not_valid;

  public function __construct(protected string $html, protected string $component_prefix)
  {
    /*
     * Remove all comments and set body(content from body tag)
     */
    $html = preg_replace("/<!--((?!-->|<!--)[\s\S])*?-->/", "", $html);
    if(preg_match('/<body>[\s\S]*<\/body>/', $html, $matches)){
      $html = $matches[0];
      $html = preg_replace('/(<body>)|(<\/body>)/', '', $html);
    }
    $this->body = trim($html);
    $this->id_body_not_valid = static::isHtmlNotValid($this->body);

//    dump(htmlspecialchars($this->html));
//    ddh($this->body);

//    $this->html_with_empty_body = $this->initDOMDocument('<div>' . $this->html . '</div>');
//    $el = $this->html_with_empty_body->getElementsByTagName('div');

//    if($el['length'] > 0)
////      dd($el);
//      ddh($this->html_with_empty_body->saveHTML());

//    if(preg_match('/^<html>[\s\S]*<\/html>$/', trim($this->html))){
//      $this->html_with_empty_body = $this->initDOMDocument($this->html);
//      $el = $this->html_with_empty_body->getElementsByTagName('body');
//      foreach($el as $node){
//
//      }
//      $item = $el->item(0);
//      $item->nodeValue = '';
//      foreach($item->childNodes as $child_node){
//        $item->removeChild($child_node);
//      }
//      dd($item);
//      dd($item->hasChildNodes());
//      dd($el->childNodes());
//      ddh($this->html);
//      $this->html_with_empty_body = $this->initDOMDocument('<div>' . $this->html . '</div>');
//      ddh($this->html_with_empty_body->saveHTML());
//    }

    /*
     * Set DOM
     */
    $this->dom = $this->initDOMDocument('<div>' . $html . '</div>');

    $this->xp = new \DOMXPath($this->dom);
    foreach ($this->xp->query('//comment()') as $comment) {
      $comment->parentNode->removeChild($comment);
    }

//    dd(111);
//    ddh($this->dom->saveHTML());

//    $doc = new DOMDocument();
//    $doc->loadXML($content);

//    $el = $doc->getElementsByTagName('body');
//
//    if($el["length"] > 0)
//    ddh($el[0]->nodeValue);
//    dd($el[0]->nodeValue);
//    dd($doc);
//
//    $el = $doc->getElementsByTagName('body');

//    $xpath = new DOMXpath($doc);
//
//    $cp = $this->component_prefix;
//    $elements = $xpath->query("//*[starts-with(local-name(),'{$cp}')][not(ancestor::*[starts-with(local-name(),'{$cp}')])]");

//    dd($el[0]->nodeValue);

//    $xml = new \SimpleXMLElement($this->content);
//    $cp = $this->component_prefix;
//    dd($xml->valid());
//    $res = $xml->xpath( "//*[starts-with(local-name(),'{$cp}')][not(ancestor::*[starts-with(local-name(),'{$cp}')])]" );
//

//    $v = preg_match_all('/^(<x-([A-z._\-0-9])+>){1}(.|\n)*(<\/x-([A-z._\-0-9])+>){1}$/', $content, $ff);
//
//    dd($ff);
//    dd($this->isContentNotValid());

//    throw new \Juno\Exceptions\ViewException("Wrong content");
//    if($isContentNotValid = $this->isContentNotValid())
//      throw new ViewException("Wrong content. " . $isContentNotValid->message);

//    $this->file_content = file_get_contents($this->file_path);
//    $xml = new \SimpleXMLElement($this->content);
//    dd($content);
//    $xml = simplexml_load_string('<div>' . $content . '</div>');
//    $this->dom = new DOMDocument();
//    $this->dom = new DOMDocument();
//    $this->dom->load($this->content);              // or loadXML($str)
//    $this->dom->loadXML($this->content);              // or loadXML($str)
//    if($xml === false)
//      dd(libxml_get_errors());

//    $xml->rewind();
//    dd($xml->valid());
//    $this->root = $this->dom->documentElement;
//      dd($this->root);
//    $xml->xpath();
//    dd(111);
//    dd($xml);

//    if($isContentNotValid = $this->isContentNotValid()){
//      dd($this->content);
//      throw new ViewException("Wrong content. " . $isContentNotValid->message);
//    }
//      throw new ViewException("Wrong content. " . $isContentNotValid->message);

//    $this->xp = new DOMXPath($xml);

//    $xml = simplexml_load_string($this->content);
//    dd(libxml_get_errors());
//    dd(count(libxml_get_errors()) == 0);
//    dd($this->dom->schemaValidate());
//    echo $content;
//    exit;
  }

  public function getDirectives(string $html){
//    if(empty($html))
//      $html = $this->html;

    $directives = [];
    $pattern = "/@push\((\'|\"){1}(([A-z])+)(\'|\"){1}\)(((?!@push|@endpush)[\s\S])+)@endpush/";
    $res = preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);
    $html = preg_replace($pattern,'', $html);
//    dd(111);
    $directives["push"] = $matches;
//    dd($matches);
    //$this->html
//    $directives
    return [
      "html" => $html,
      "directives" => $directives,
    ];
  }

  public function createElementFromHtml(string $html){
    $d = Parser::initDOMDocument($html);
    $el = $this->dom->createElement('div');
    $this->dom->importNode($d->documentElement);
    $el->appendChild($this->dom->importNode($d->documentElement, TRUE));
    return $el;
  }

  static public function initDOMDocument(string $html): DOMDocument {
    $dom = new DOMDocument();
    $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);
    return $dom;
  }

  public function isBodyNotValid(): mixed {
    return $this->id_body_not_valid;
  }

//  static public function removeHtmlCommentsFromStr(string $html) : string
//  {
//    $html = preg_replace("/<!--((?!-->|<!--)[\s\S])*?-->/", "", $html);
//    return $html;
//    return $html;
//  }

  static public function isHtmlNotValid(string $html) : mixed
  {
    libxml_clear_errors();
    simplexml_load_string('<div>' . $html . '</div>');
    $libxml_errors = libxml_get_errors();
    libxml_clear_errors();

    if(count($libxml_errors) > 0){
      $err = $libxml_errors[0];
      $err = explode('line', $err->message)[0];
      $err = trim($err);
      return $err;
    }

    return false;
  }

  public function getComponentPrefix() : string
  {
    return $this->component_prefix;
  }

  public function createDocumentFragment()
  {
//    createElement
    return $this->dom->createElement('div');
//    return $this->dom->createDocumentFragment();
  }

  public function getTopComponentsNodes()
  {
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

  public function isNodeHasParentComponent(DOMElement $node) : bool
  {
    if(empty($node->parentNode))
      return false;

//    $node = $node->parentNode;
    do{
      $node = $node->parentNode;
      if(empty($node) || empty($node->tagName))
        break;
//      dump($node);
//      $node = $node->parentNode;
//      dd($node);
      $tag = $node->tagName;
//      dd($tag);
//      dd(str_starts_with($tag, $this->component_prefix));
      if(str_starts_with($tag, $this->component_prefix))
        return true;
//
////      dump($tag);

//      $node = $node->parentNode;
    }while(true);
//    dd(5555);


    return false;
  }

  public function saveHTML(DOMElement $node = null, bool $strip_top_tag = true)
  {
//    dd($this->dom->saveHTML());
    $html = !empty($node) ? $this->dom->saveHTML($node) : $this->dom->saveHTML();
    if(!$strip_top_tag || empty($node))
      return $html;

//    dd(111);

    $tag_name = $node->tagName;
    $tag_start = "<{$tag_name}>";
    $tag_end = "</{$tag_name}>";
    $html = rtrim(ltrim($html, $tag_start), $tag_end);

//    dd($html);

    return $html;
  }

}