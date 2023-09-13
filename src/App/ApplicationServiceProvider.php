<?php
namespace Juno\App;

use Juno\Contracts\ServiceProviderContract;
use Juno\Support\ServiceProvider;
use Juno\Request\Request;
use Juno\Routing\Router;
use App\Kernel;
//use Juno\Kernel as MainKernel;
use Juno\Helpers\Arr\Arr;
use Juno\Helpers\Str as StrHelper;
//use Juno\Dotenv\Dotenv;
use Juno\Database\Manager;
use Juno\Database\Connection as PDOConnection;
use Juno\Response\Response;
use Juno\View\View;
use Juno\Sessions\Session;
use Juno\Sessions\FlashSession;
use Juno\Sessions\Contracts\GlobalSession;
use Juno\Sessions\Contracts\AuthSession;
use Juno\Sessions\Contracts\FlashSessionContract;
use Juno\Sessions\Contracts\AppSession;
use Juno\Validating\Validator;
use Juno\Cookie\Cookie;

class ApplicationServiceProvider extends ServiceProvider implements ServiceProviderContract{

  public function register() : void
  {
//    $this->app->singleton(Dotenv::class, fn(string $path) => new Dotenv($path));
    $this->app->singleton('app', fn() => $this->app);
    $this->app->singleton(Manager::class, fn(PDOConnection $conn) => new Manager($conn));
    $this->app->singleton(Router::class, fn() => new Router());
    $this->app->bind(Validator::class, fn() => new Validator());
    $this->app->singleton(Kernel::class, fn() => new Kernel());
    $this->app->singleton(Request::class, fn() => new Request());
    $this->app->singleton(Arr::class, fn() => new Arr());
    $this->app->singleton(StrHelper::class, fn() => new StrHelper());

    //Sessions
    $this->app->singleton(GlobalSession::class, fn() => new Session());
    $this->app->singleton(AuthSession::class, fn() => new Session('auth'));
    $this->app->singleton(FlashSessionContract::class, fn() => new FlashSession('flash'));
    $this->app->singleton(AppSession::class, fn() => new Session('app'));

    //Cookie
    $this->app->singleton(Cookie::class, fn() => new Cookie());

//    $this->app->singleton(Dotenv::class, fn($path) => new Dotenv($path));
//    $this->app->singleton(Dotenv::class, fn(string $path) => new Dotenv($path));
//    $this->app->singleton(Dotenv::class, fn(string $path) => new Dotenv($path));

    $this->app->bind(Response::class, fn() => new Response());
//    $this->app->bind(View::class, fn(string $path, array $params = [], bool $is_core = false) => new View($path, $params, $is_core));

    $this->app->bind(View::class, function(string $path, array $params = [], bool $is_core = false) {
      return new View($path, $params, $is_core);
    });
  }

  public function boot() : void
  {
//    \App::make(Manager::class);

    \App::makeWith(Manager::class, [
      'conn' => new PDOConnection(
        database: getenv('DB_DATABASE'),
        username: getenv('DB_USERNAME'),
        host: getenv('DB_HOST'),
        port: getenv('DB_PORT'),
        password: getenv('DB_PASSWORD'),
        charset: 'utf8',
        collation: 'utf8_general_ci'
      )
    ]);
  }

}