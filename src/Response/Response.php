<?php
namespace Juno\Response;

class Response {

  protected array $headers = [];

  protected string $content = '';

  protected int $code = 200;

  public function setHeader(array|string $name, string $value = null) : self
  {
    return $this->header($name, $value);
  }

  public function addHeader(array|string $name, string $value = null) : self
  {
//    dd(111);
    return $this->header($name, $value, true);
  }

  public function header(array|string $name, string $value = null, bool $merge = false) : self
  {
    if(!empty($value) && is_string($name))
      $name = [$name => $value];

    if(!is_array($name))
      return $this;

    $this->headers = $merge ? array_merge($this->headers, $name) : $name;

//    dd($this->headers);

    return $this;
  }

  public function content(string|array $cont) : self
  {
    if(is_array($cont))
      return $this->json($cont);

    $this->content = $cont;
    return $this;
  }

  public function text(string|int $cont) : self
  {
    return $this->addHeader([
      'Content-Type' => 'text/plain',
    ])->content($cont);
  }

  public function textHtml(string|int $cont) : self
  {
    return $this->addHeader([
      'Content-Type' => 'text/html',
    ])->content($cont);
  }

  public function json(mixed $cont) : self
  {
    $cont = json_encode($cont);
    return $this->addHeader([
      'Content-Type' => 'application/json',
    ])->content($cont);
  }

  public function code(int $code) : self
  {
    $this->code = $code;
    return $this;
  }

  public function send(int $code = null) : void
  {
    if(!empty($this->headers)){
      foreach($this->headers as $k => $v){
        header($k . ': ' . $v);
      }
    }

//    dd($code);

    http_response_code($code ?? $this->code);

    echo $this->content;
    exit;
  }

}