<?php
namespace Juno\App;

use Juno\Support\Singleton\Singleton;
use Juno\Router\Exceptions\PageNotFoundException;
use Juno\Router\Executor as RouteExecutor;
use Juno\App\App;

//use Core\App\AppInit;
class Kernel extends Singleton{

  protected $app = null;

  public function run() : void
  {
    try {

      include_once(ROUTER_PATH . 'web.php');

//      $this->app = new App();

      $this->dispatchRouter();

    } catch (\Exception $e) {
      echo 'Exception abgefangen: ',  $e->getMessage(), "\n";
    }
  }

  protected function dispatchRouter() : void
  {
//    dump('AppInit@dispatchRouter');

    $url = parse_url($_SERVER['REQUEST_URI']);
    $method = Request::getMethod();
    $request_uri = Request::getRequestUri();

    $route = Router::getMatchRoute($request_uri, $method);

    if(empty($route))
      throw new PageNotFoundException('Page not found.');

    $route->resolve();

//    dd($route);
  }

}