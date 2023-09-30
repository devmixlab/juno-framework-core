<?php
namespace Juno\View;

class Directives{

  protected $list = [];
  protected $patterns = [
    "push" => "/@push\((\'|\"){1}(([A-z])+)(\'|\"){1}\)(((?!@push|@endpush)[\s\S])+)@endpush/",
    "prepend" => "/@prepend\((\'|\"){1}(([A-z])+)(\'|\"){1}\)(((?!@prepend|@endprepend)[\s\S])+)@endprepend/",
    "include" => "/@include\((\'|\"){1}(([A-z\.0-9_-])+)(\'|\"){1}(,(( |\n)*)?\[(((?!\]( |\n)?\))[\s\S])*)?\])?(( |\n)*)?\)/"
  ];

  public function __construct(protected InitView $view){}

  public function stack(string $html): string {
    $html = HtmlHandler::removeHtmlComments($html);
    ["directives" => $directives, "html" => $html] = $this->getDirectives($html);
    $this->append($directives);

    return $html;
  }

  public function apply(string $html): string {
//    dd($this->list);
//    $stack = [];
    foreach($this->list as $k => $v){
      if($k == 'stack'){
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
          $pattern = "/@stack\((\'|\"){1}({$vv['name']})(\'|\"){1}\)/";
          $html = preg_replace($pattern, $vv['content'], $html);
        }

//        $pattern = "/@stack\((\'|\"){1}([a-z0-9]*)(\'|\"){1}\)/";
//        $html = preg_replace($pattern, '', $html);

      }else if($k == 'include'){
        foreach($v as $kk => $vv){
          $params = $this->view->params();
          if(!empty($vv[8])){
            $eval = trim($vv[8]);
            $eval = "return [{$eval}];";

            $eval = preg_replace("/=&/", '=>', $eval);
            $eval = preg_replace("/gt;/", '', $eval);
            $include_params = eval($eval);
//            dd($include_params);
            $params = array_merge($params, $include_params);
//            dd($params);
          }

          $res_html = new View($vv[2], $params);
          $pos = strpos($html, $vv[0]);
          if ($pos !== false) {
            $html = substr_replace($html, $res_html->make(), $pos, strlen($vv[0]));
          }
        }
      }
    }

    $pattern = "/@stack\((\'|\"){1}([a-z0-9]*)(\'|\"){1}\)/";
    $html = preg_replace($pattern, '', $html);

    return $html;
  }

  protected function append(array $directives): void {
    if(empty($directives))
      return;

    foreach($directives as $k => $v){
      if(array_key_exists($k, $this->list) && is_array($this->list[$k])){
        $this->list[$k] = array_merge($this->list[$k], $v);
      }else{
        $this->list[$k] = $v;
      }
    }
  }

  /**
   * Gets directives found in given html
   * and removes some directives from html
   *
   * @return array<string, array>
   */
  public function getDirectives(string $html): array {
    $directives = [];

    foreach (['push','prepend'] as $key){
      $res = preg_match_all($this->patterns[$key], $html, $matches, PREG_SET_ORDER);
      if(!empty($res)){
        $html = preg_replace($this->patterns[$key],'', $html);
        if(empty($directives['stack']))
          $directives['stack'] = [];

        if($key == 'prepend'){
          $directives['stack'] = array_merge(array_reverse($matches), $directives['stack']);
        }else{
          $directives['stack'] = array_merge($directives['stack'], $matches);
        }
      }
    }

//    dd($directives);

    $res = preg_match_all($this->patterns['include'], $html, $matches, PREG_SET_ORDER);
//    dd($matches);
    if(!empty($res)){
//      $html = preg_replace($pattern,'', $html);
      $directives["include"] = $matches;
    }

//    dd($directives);

    return [
      "html" => $html,
      "directives" => $directives,
    ];
  }

}