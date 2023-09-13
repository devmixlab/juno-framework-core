<?php
namespace Juno\View;

use DOMDocument;
use DOMXPath;
use DOMElement;

class Parser{

  protected $component_prefix = 'x-';
  protected $dom;
  protected $xp;
  protected $root;

  public function __construct(protected string $content)
  {
//    $this->file_content = file_get_contents($this->file_path);
    $this->dom = new DOMDocument();
//    $this->dom = new DOMDocument();
//    $this->dom->load($this->file_content);              // or loadXML($str)
    $this->dom->loadXML($this->content);              // or loadXML($str)
    $this->root = $this->dom->documentElement;
    $this->xp = new DOMXPath($this->dom);
  }

  public function getComponentPrefix() : string
  {
    return $this->component_prefix;
  }

  public function createDocumentFragment()
  {
    return $this->dom->createDocumentFragment();
  }

  public function getTopComponentsNodes()
  {
    $component_tags = [];
    $cp = $this->component_prefix;
    foreach ( $this->xp->query( "//*[starts-with(local-name(),'{$cp}')][not(ancestor::*[starts-with(local-name(),'{$cp}')])]" ) as $node ) {
//      dd($node->tagName);

//      dd($node);
//      array_push( $array, $dom->saveXML( $node ), "\n" );
      if(!in_array($node->tagName, $component_tags)){
        $component_tags[] = $node->tagName;
//        array_push( $array, $node);
      }
    }

//    $nodes = $this->root->getElementsByTagName($nodeName);
//    dd($component_tags);

    $nodes = [];
    foreach($component_tags as $tag){
      $node_list = $this->root->getElementsByTagName($tag);
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
    $html = !empty($node) ? $this->dom->saveHTML($node) : $this->dom->saveHTML();
    if(!$strip_top_tag || empty($node))
      return $html;

    $tag_name = $node->tagName;
    $tag_start = "<{$tag_name}>";
    $tag_end = "</{$tag_name}>";
    $html = rtrim(ltrim($html, $tag_start), $tag_end);

    return $html;
  }

}