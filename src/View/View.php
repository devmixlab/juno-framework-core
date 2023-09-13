<?php
namespace Juno\View;

use DOMDocument;

class View{

  protected string $view_path;
  protected Parser $parser;

  protected string $html;

  public function __construct(protected string $path, protected array $params = [], bool $is_core = false)
  {
    if(strpos($path, DIRECTORY_SEPARATOR)){
      $this->view_path = $path;
    }else if(!$is_core && defined('VIEWS_PATH')){
      $this->view_path = VIEWS_PATH . str_replace(".",DIRECTORY_SEPARATOR, $path) . '.php';
    }else if($is_core){
//      dd(__DIR__);
      $this->view_path = __DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR;
      $this->view_path .= str_replace(".",DIRECTORY_SEPARATOR, $path) . '.php';
//      dd($this->view_path);
    }
//    $this->view_path = VIEWS_PATH . str_replace(".",DIRECTORY_SEPARATOR, $path) . '.php';
//    $this->parser = new Parser($this->view_path);

//    $path_arr = explode('.', $path);
//    dd($this->view_path);
//    $path = rtrim($path, .);
  }

  public function makePathFromTagName(string $tag_name, string $prefix) : string
  {
    $str = ltrim($tag_name, $prefix);
    $str = str_replace('.', DIRECTORY_SEPARATOR, $str);
    return VIEWS_PATH . $str . '.php';
  }

  public function loadComponentFile(string $path, string $slot = null)
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
//      require($path);
      require(__DIR__ . DIRECTORY_SEPARATOR . 'temp.php');
      return ob_get_clean();
    };

    return $load($this->params);
  }

  public function resolveContent()
  {
//    $file_content = file_get_contents($path);
    $parser = new Parser('<div>' . $this->html . '</div>');

    $nodes = $parser->getTopComponentsNodes();
    foreach($nodes as $node){
      $tag_name = $node->tagName;
      $path = $this->makePathFromTagName($tag_name, $parser->getComponentPrefix());

//      dd($path);

      $html = $parser->saveHTML($node);

//      dd($html);

      $html = $this->loadComponentFile($path, $html);

      $fragment = $parser->createDocumentFragment();
      $fragment->appendXML($html);

//      $dom = new DOMDocument();
//      $dom->loadXML('<div>" . $html . "</div>");

      $node->parentNode->replaceChild($fragment, $node);

//      echo $html;
//      die();

//      $node->nodeValue = "<div>" . $html . "</div>";

      $this->html = $parser->saveHTML();

//      echo $html;
//      die();

//      $parser = new Parser('<div>' . $comp . '</div>');
////      dd(11);
//      $nodes = $parser->getTopComponentsNodes();
//      dd($nodes);
      $this->resolveContent();
//      $comp = $this->loadComponent($path, $html);

//      dd($comp);
//
//      dd($parser->saveHTML($path, $node));
//      dd($path);
//      dump($path);
    }
  }

  public function make() : string
  {
    libxml_use_internal_errors(true);
//    $this->html = file_get_contents($this->view_path);
    $this->html = $this->loadComponentFile($this->view_path);
    $this->resolveContent();
//
    return $this->html;
//    die();
//
//    dd($this->html);
//
//    $file_content = file_get_contents($this->view_path);
////    dd($file_content);
//
//    $parser = new Parser($file_content);
//
//    $nodes = $parser->getTopComponentsNodes();
//
//    dd($nodes);
//
//    foreach($nodes as $node){
////      dd($node);
//      $tag_name = $node->tagName;
//      $path = $this->makePathFromTagName($tag_name, $parser->getComponentPrefix());
//
//      $html = $parser->saveHTML($node);
//
//      $comp = $this->loadComponentFile($path, $html);
////      $comp = $this->loadComponent($path, $html);
//
//      dd($comp);
//
//      dd($parser->saveHTML($path, $node));
//      dd($path);
////      dump($path);
//    }
//
//    dd($nodes);
//
////    dd($this->doc->saveHTML($node));
//
//    dd($parser->saveHTML($nodes[0]));
//
////    dd($nodes[0]->ownerDocument->saveHTML());
////    foreach($nodes as $node){
////      $node_name = $node->nodeName;
////      dd($node_name);
//////      $parser->isNodeHasParentTag();
////    }
//
//    dd($nodes);
//
//    function get_tag( $tagname, $xml ) {
//
////      $attr = preg_quote($attr);
////      $value = preg_quote($value);
//
//      $tag_regex = "/<{$tagname}>(.*?)<\\/{$tagname}>/si";
//
////      dd($tag_regex);
//
//      preg_match($tag_regex,
//        $xml,
//        $matches);
//
//      return $matches;
//    }
//
//    $yourentirehtml = file_get_contents($this->view_path);
////    $extract = get_tag('', $yourentirehtml);
////    echo $extract;
//
//    $dom = new DOMDocument();
//////    $dom->loadHTMLFile($this->view_path);    // or loadHTML($str)
//    $dom->load($this->view_path);              // or loadXML($str)
//    $root = $dom->documentElement;
//    $xp = new \DOMXPath($dom);
////
//    $array = [];
//    foreach ( $xp->query( "//*[starts-with(local-name(),'x-')][not(ancestor::*[starts-with(local-name(),'x-')])]" ) as $node ) {
////      dd($node->tagName);
//
////      dd($node);
////      array_push( $array, $dom->saveXML( $node ), "\n" );
//      array_push( $array, $node);
//    }
//
//    $nodeName = $array[0]->nodeName;
//
//    $markers = $root->getElementsByTagName($nodeName);
//
//    foreach($markers as $k => $v){
//      dd($v->parentNode->parentNode->parentNode);
//    }
//
//    dd($markers);
//    dd($array);
////
//////    $dom->load($array[0]->nodeValue);
//////    echo $array[0]->nodeValue;
////////    echo $array[0]->saveXML();
//////    die();
////
//////    $nodeName = $array[0]->nodeValue;
////    $nodeName = $array[0];
////
//////    $dom->appendChild($array[0]);
//////    $el = $dom->getElementById('my');
//////    dump($el);
//////    echo $dom->saveXML();
//////    die();
////
////    echo dom_import_simplexml($nodeName);
////    die();
////
////    dd($nodeName);
////    dd($array);
//
//    $extract = get_tag($array[0]->nodeName, $yourentirehtml);
//    dd($extract);
//    die();

//
////    $dd = $dom->getElementByTagName($nodeName);
//
//    dd($dd);
//
//    die();
//
////    $table = $dom->getElementById('mytable-id');    // DOMElement
////    $lines = $dom->getElementsByTagName('x-layouts.layout');    // DOMNodeList
////    $links = $dom->getElementsByTagName('a');
//
////    $xpath = new DOMXpath($dom);
////    $tables = $xpath->query("//table[contains(@class,'mytables-class')]");
//
////    ob_start();
////    include $this->view_path;
////    $buffer = ob_get_clean();
//
////    echo $lines->saveHTML();;
//
//    dd(count($lines));
//
////    foreach($lines as $node){
////      echo 1;
////      echo $node->nodeValue;
////    }
//
//    dd(11);
//
//    return $buffer;
  }

}