<?php

class JWTModel extends DatabaseModel
{

  public function store($jti, $jwt, $user_id)
  {
    $sql = "INSERT INTO tokens (jti, jwt, created_at, updated_at, users_id) VALUES (:jti, :jwt, NOW(), NOW(), :user_id)";
    $placeholders = [
      'jti' => $jti,
      'jwt' => $jwt,
      'user_id' => $user_id
    ];
    $res = $this->run_query($sql, $placeholders)->rowCount();

    if ($res > 0)
    {
      return true;
    }
  }

  public function refresh()
  {
    //
  }

  public function revoke($jti)
  {
    $sql = "DELETE FROM tokens WHERE jti = :jti";
    $res = $this->run_query($sql, ['jti' => $jti])->rowCount();

    if ($res > 0)
    {
      return true;
    }
  }

  // column 'id' will change to 'jti'
  public function find_by_id($jti)
  {
    $sql = "SELECT * FROM tokens WHERE jti =:jti";
    $res = $this->run_query($sql, ['jti' => $jti])->fetch();
    return $res;
  }

  public function find_by_token($jwt)
  {
    $sql = "SELECT * FROM tokens WHERE jwt =:jwt";
    $res = $this->run_query($sql, ['jwt' => $jwt])->fetch();
    return $res;
  }

  // this is just a model
  public function get_keys($url)
  {
    return json_decode(file_get_contents(json_decode(file_get_contents($url, false, stream_context_create([
      'http'=>[
        'method'=>'GET',
        // 'header'=>''
      ]
    ])), 1)['jwks_uri'], false, stream_context_create([
      'http'=>[
        'method'=>'GET',
        // 'header'=>''
      ]
    ])), 1)['keys'];
  }
}
