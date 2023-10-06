<?php
namespace Juno\Validating;

//use Juno\Validating\Exceptions\ValidationException;
//use Juno\Validating\Rules\Required;
//use Juno\Request\Request;
//use App\Kernel;
use Arr;

class Errors {

  public function __construct(protected array $data){}

  public function has(string $name = null): bool {
    if(is_null($name))
      return !empty($this->data);

    return Arr::existsByDotPattern($this->data, $name);
  }

  public function all(): array {
    return $this->data;
  }

  public function first(string $name) {
    if(!Arr::existsByDotPattern($this->data, $name))
      return null;

    $errs = Arr::getByDotPattern($this->data, $name);
    if(!is_array($errs) || empty($errs))
      return null;

    return array_shift($errs);
  }

}