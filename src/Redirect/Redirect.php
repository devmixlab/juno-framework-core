<?php
namespace Juno\Redirect;

//use core\redirect\exceptions\BadRedirectException;
//use \core\session\enums\CoreKeys as SessionCoreKeys;
use Juno\Facades\Url;
use Juno\Facades\Arr;
use Juno\Response\Response;
use Juno\Validating\Validator;

// use core\redirect\Redirect;
class Redirect{

  protected string $to_url;
  protected array $with = [];

//  public function handle(): void {
//    if(empty($this->to_url))
//      return;
//
////    if(!empty($this->with)){
////      // dd($this->with);
////      \Session::flash($this->with);
////    }
//
//    header('Location: ' . $this->url_to);
//    die();
//  }

  public function makeResponse(): Response {
    $response = response()->header('Location', $this->to_url);
    if(!empty($this->with))
      $response->with($this->with);

    return $response;
  }

  public function back(Validator $validator = null): self {
    if(!empty($validator) && !$validator->valid()){
      $this->withErrors($validator->errors())
        ->withInput($validator->data());
    }

    $this->to_url = Url::previous();
    return $this;
  }

  public function toUrl(string $url): self {
    $this->to_url = $url;
    return $this;
//    return response()->header('Location', $url);
  }

  // public function uri($uri){
  //     if(\Url::isCurrentPathRelative($uri)){
  //         $path_array = \Request::getPathArray();
  //         $uri_array = \Url::getPathParts($uri);
  //         array_pop($path_array);
  //         $new_uri_array = array_merge($path_array, $uri_array);
  //         $uri = \Url::composeRootPathRelative($new_uri_array);
  //     }
  //     header('Location: ' . \App::config('root') . $uri);
  //     die();
  // }

  /**
   *   Sets url to redirect to by name of route and passed parameters
   *
   *   @param string $name - route name
   *   @param array $params - params for route
   *   @return self
   */
//  public function route(string $name, array $params = []) : self {
//    $this->url_to = route($name, $params, true);
//    return $this;
//  }

//  public function url(string $url): self {
//    $this->url_to = $url;
//    return $this;
//  }

  public function with($key, $value = null){
    if(!is_string($key) && !is_array($key))
      return $this;

    if(is_string($key))
      $key = [$key => $value];

    $this->with = $key;
//    $this->with = array_merge($this->with, $key);
    return $this;
  }

  public function withInput(array $data){
    $this->with = Arr::setByDotPattern($this->with, 'input', $data);
    return $this;
  }

  public function withErrors(array $data): self {
    $this->with = Arr::setByDotPattern($this->with, 'errors', $data);
    return $this;
  }

}