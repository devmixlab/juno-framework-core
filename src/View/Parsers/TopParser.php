<?php
namespace Juno\View\Parsers;

//use Juno\View\ComponentParser;
use Juno\View\HtmlHandler;
use Closure;

//use DOMDocument;
//use DOMXPath;
//use DOMElement;
//use Juno\Exceptions\AppException;
//use Juno\Exceptions\ViewException;

class TopParser{

  protected string $html;

  public function __construct(string $html){
    $html = HtmlHandler::removeHtmlComments($html);
    $this->html = $this->parse($html);
  }

  protected function parse(string $html): string {
    // Replaces {{ {data} }} with executable php tags
    $html = preg_replace('/\{\{(((?!\{\{|\}\})[\s\S])*)\}\}/i','<?= $1 ?>', $html);
    // Replaces @csrf with input
    $html = str_replace("@csrf", '<input type="hidden" name="__csrf" value="' . \Csrf::get() . '">', $html);
    // Replaces @method('method') with input
    $html = preg_replace("/@method\(('|\")[ ]*([A-z]+)[ ]*(\g1)\)/", '<input type="hidden" name="__method" value="$2">', $html);
    // Replaces @php {statements} @endphp with open|close php tags
    $html = preg_replace("/@php(((?!@php|@endphp)[\s\S])*)@endphp/", '<?php $1 ?>', $html);
    // Replaces @foreach {statements} @endforeach with open|close php foreach tags
    $html = preg_replace("/@foreach\((((?!@endforeach|@foreach)[\s\S])+)\)(((?!@foreach|@endforeach)[\s\S])*)@endforeach/", '<?php foreach($1): ?> $3 <?php endforeach; ?>', $html);
    // Replaces @foreach {statements} @endforeach with open|close php foreach tags
    $html = preg_replace("/@foreach\((((?!@endforeach|@foreach)[\s\S])+)\)(((?!@foreach|@endforeach)[\s\S])*)@endforeach/", '<?php foreach($1): ?> $3 <?php endforeach; ?>', $html);
    // Replaces @for {statements} @endfor with open|close php for tags
    $html = preg_replace("/@for\((((?!@endfor|@for)[\s\S])+)\)(((?!@for|@endfor)[\s\S])*)@endfor/", '<?php for($1): ?> $3 <?php endfor; ?>', $html);

    return $html;
  }

  public function html(): string {
    return $this->html ?? '';
  }

}