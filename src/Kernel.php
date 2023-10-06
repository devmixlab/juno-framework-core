<?php
namespace Juno;

use Juno\Exceptions\AppException;
use Juno\Exceptions\PageNotFoundException;
use InvalidArgumentException;
//use Request;
use Juno\Facades\Request;
use Router;
use Juno\Dotenv\Dotenv;
use Exception;
use ErrorException;
use PDOException;
use BadMethodCallException;
use Juno\Exceptions\Handler as ExceptionHandler;
use Arr;
use Juno\Exceptions\ViewException;

class Kernel{

  private $run = false;

  static protected $service_providers = [
    \Juno\App\ApplicationServiceProvider::class,
  ];

  static protected $validation_rules = [
    'required' => \Juno\Validating\Rules\Required::class,
    'string' => \Juno\Validating\Rules\Str::class,
    'numeric' => \Juno\Validating\Rules\Numeric::class,
    'integer' => \Juno\Validating\Rules\Integer::class,
    'email' => \Juno\Validating\Rules\Email::class,
    'array' => \Juno\Validating\Rules\Arr::class,
    'same' => \Juno\Validating\Rules\Same::class,
    'in' => \Juno\Validating\Rules\In::class,
  ];

  static public function getValidationRules(string $name = null) : string|array|null
  {
    $validation_rules = static::$validation_rules;
    if(!empty(static::$app_validation_rules) && is_array(static::$app_validation_rules))
      $validation_rules = array_merge(static::$app_validation_rules, $validation_rules);

    return empty($name) ? $validation_rules : Arr::getByDotPattern($validation_rules, $name);
  }

  static public function getServiceProviders() : array
  {
    if(empty(static::$app_service_providers) || !is_array(static::$app_service_providers))
      return static::$service_providers;
    return array_merge(static::$app_service_providers, static::$service_providers);
  }

  public function webMiddleware() : array
  {
    return Arr::getByDotPattern($this->middleware_groups, 'web', []);
  }

  public function run() : void
  {
    if($this->run)
      return;

    $this->run = true;

//    $onError = function ($level, $message, $file, $line) {
//      dd(2222);
////      throw new ErrorException($message, 0, $level, $file, $line);
//    };

    $onShutdown = function () {
      $error = error_get_last();

      if ($error === null)
        return;

//      dd($error);

      $e = new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
      $exception_handler = new ExceptionHandler($e);
      response()
        ->textHtml($exception_handler->render())
        ->send($e->getCode());
    };

    try {
//      dd(11);
//      throw new ViewException("fsdfsdf");

//      dd(11);
//      set_error_handler($onError);
      register_shutdown_function($onShutdown);
//      error_reporting(0);

      (new Dotenv(ROOT_PATH . '.env'))->load();
      \App::bootServiceProviders();

      include_once(ROUTER_PATH . 'web.php');

      $this->dispatchRouter();

    }
//    catch (InvalidArgumentException $e) {
//      echo 'InvalidArgumentException: ' . $e->getMessage();
//    }
//    catch (InvalidArgumentException $e) {
//      echo 'InvalidArgumentException: ' . $e->getMessage();
//    }
    catch (\ArgumentCountError|ErrorException|Exception|PDOException $e) {
      if($e instanceof PageNotFoundException)
        abort(404);
//        dd(111);

//      dd(434);
      $exception_handler = new ExceptionHandler($e);
//      $exception_handler->();
//      dd(312312);
      response()
        ->textHtml($exception_handler->render())
        ->send((int)$e->getCode());


//      dd(111);
//      response()
//        ->exception($e)
////        ->text('sdfsdf')
//        ->send();
//      response()
//        ->textHtml($e)
////        ->text('sdfsdf')
//        ->send($e->getCode());
//      echo $e;
    }
//    catch (PDOException $e) {
//      echo $e;
//    }
//    finally {
//      restore_error_handler();
//    }
//    catch (PageNotFoundException $e) {
//      dd(111);
//    }
//    catch (PDOException $e) {
//      echo $e;
//    }
// catch (BadMethodCallException $e) {
//      echo $e;
//    }

//    dd(11);
  }

  private function dispatchRouter() : void
  {
    $method = Request::method();
    $path = Request::path();

    $route = Router::getMatchRoute($path, $method);

//    dd($route);

    if(empty($route))
      throw new PageNotFoundException();

    $route->resolve();
  }

}