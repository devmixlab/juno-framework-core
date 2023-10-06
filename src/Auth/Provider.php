<?php
namespace Juno\Auth;

use Juno\Database\Model;

class Provider {

  protected $name;
  protected $model_class;
  protected $user;

  function __construct(protected array $config) {
    if(!empty($config['name']))
      $this->name = $config['name'];

    if(!empty($config['model']))
      $this->model_class = $config['model'];

//    dd(auth_session()->get('providers.usrs.user'));
    $user = auth_session()->get('providers.' . $this->name . '.user');
//    dd($user);
    if(!empty($user))
      $this->user = new $this->model_class($user);
  }

  public function attempt(string $login, string $password): array|bool {
    $user = $this->model_class::where("email", $login)->first();

    $is_succeed = $user != false && password_verify($password, $user->password);

    if($is_succeed)
      $this->authorizeWeb($user);

//    dd($is_succeed);
    return $is_succeed;
  }

//  public function authorize(Model $user): void {
//    $this->authorizeWeb($user);
//  }

  public function authorizeWeb(Model $user): void {
    $this->user = $user;
//    dd($user);
    auth_session()->put('providers.' . $this->name . '.' . 'user', $user->toArray());
  }

  public function logout(): void {
    auth_session()->push('providers.' . $this->name, null);
    $this->user = null;
  }

  public function isAuth(): bool {
    return !empty($this->user);
  }

  public function user(): Model|null {
    return $this->isAuth() ? $this->user : null;
  }

}