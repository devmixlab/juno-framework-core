<?php
namespace Juno\Exceptions;

use Exception;

class Handler
{

  public function __construct(protected $e)
  {

  }

  public function render() : string
  {
    $str = core_view('exception', [
      'exception' => get_class($this->e),
      'message' => $this->e->getMessage(),
      'code' => $this->e->getCode(),
      'trace' => $this->makeCustomTrace(),
    ])->make();

    return $str;
  }

  protected function makeCustomTrace(string $format = 'html') : string
  {
    $out = '';
    $trace_arr = $this->e->getTrace();

    if(empty($trace_arr))
      return $out;

    $i = 0;
    foreach($trace_arr as $trace){
      $str = '';
//      if(empty($trace['file']))
//        dd($trace);
      if(!empty($trace['file'])){
//        $str .= '<div style="border-bottom: 1px solid #ccc;">';
        $str .= '<div class="fs-7">';
        if($i != 0)
          $str .= '<div class="pt-3"></div>';
        $str .= $trace['file'] . " <span class='text-primary'>(" . $trace['line'] . ")</span>";
        $str .= '</div>';
      }

      if(!empty($trace['class']) || !empty($trace['type']) || !empty($trace['function'])){
        $str .= "<div class='text-muted'>";
        if(!empty($trace['class']))
          $str .= $trace['class'];
        if(!empty($trace['type']))
          $str .= $trace['type'];
        if(!empty($trace['function']))
          $str .= $trace['function'];
        $str .= "</div>";
      }

      $out .= "<div>$str</div>";
      $i++;
    }

//    $compose_trace = function(array $trace) use (&$out, &$trace_arr) {
//      $out .= $first_trace['file'] . " <span class='text-warning'>(" . $first_trace['line'] . ")</span>";
//    }

//    $first_trace = array_shift($trace_arr);



//    $method = '';
//    if(!empty())
//
//    $trace_str .= $first_trace['file'] . " <span class='text-warning'>(" . $first_trace['line'] . ")</span>";
//
//    dd($first_trace);
    return $out;
//    echo $trace_str;
//    exit;
//
//    dd($first_trace);
  }

}