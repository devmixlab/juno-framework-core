<?php
namespace Juno\Sessions;

use SessionHandlerInterface;
use App\Models\Session as SessionModel;

class DBDriver implements SessionHandlerInterface {

  public function open($save_path, $session_name): bool
  {
    return true;
  }

  public function close(): bool
  {
    return true;
  }

  public function read($id) : string|false
  {
    $res = SessionModel::where('id', $id)->value('data');
    return $res ?? '';
  }

  public function write($id, $data): bool
  {
    return SessionModel::replace([
      'id' => $id,
      'data' => $data,
      'access' => date('YmdHis'),
    ]);
  }

  public function destroy($id): bool
  {
    return SessionModel::where('id', $id)->delete();
  }

  public function gc($maxlifetime): int|false
  {
    $ts = date('YmdHis', time() - $maxlifetime);
    $q = $this->dbh->prepare("DELETE FROM $this->db_table WHERE access < ?");
    $q->bindParam(1, $ts);
    if ( $q->execute() )
      return $q->rowCount();
    return false;
  }
}