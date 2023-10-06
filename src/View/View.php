<?php
namespace Juno\View;

class View extends InitView{

  public function make(): string {
    $this->resolveHtml();

    $this->html = $this->directives->apply($this->html);
//    ddh($this->html);

    return $this->html;
  }

}