<?php
namespace Juno\Validating\Contracts;

use Juno\Request\Request;

interface RuleContract{

  public function validate(Request $request, string $name) : bool;

}