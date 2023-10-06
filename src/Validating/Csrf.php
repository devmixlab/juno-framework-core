<?php
namespace Juno\Validating;

//use Arr;

class Csrf {

//  protected string $csrf_to_validate;
  protected string|null $csrf = null;
  protected string $prop = '__csrf';
  protected int $span = 60 * 1;

  public function __construct(){
//    auth_session()->forget($this->prop);
//    dd($this->isSessCsrfValid());
//    dump(auth_session()->get($this->prop));
    if($this->isSessCsrfValid()){
      $this->csrf = auth_session()->get($this->prop . ".hash");
      auth_session()->put($this->prop . ".release", time());
    }else{
      $this->csrf = \Hash::make(uniqid(mt_rand(), true));
      auth_session()->put($this->prop, [
        "hash" => $this->csrf,
        "release" => time(),
      ]);
    }
  }

  public function isSessCsrfValid(){
    if(!auth_session()->has($this->prop))
      return false;

    $csrf = auth_session()->get($this->prop);
//    dd(time() > ($csrf['release'] + $this->span));
    if(empty($csrf['hash']) || empty($csrf['release']) || !is_int($csrf['release']) ||
    time() > ($csrf['release'] + $this->span))
      return false;

//    dd($csrf);

    return true;
  }

  public function get(){
    return $this->csrf;
  }

  public function validate(string $csrf): bool {
    return $csrf == $this->csrf;
  }

}