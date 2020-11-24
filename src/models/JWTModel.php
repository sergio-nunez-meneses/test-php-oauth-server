<?php

class JWTModel extends DatabaseModel
{

  // this method will be replaced by store()
  public function store_id($jti)
  {
    $sql = "INSERT INTO tokens (id, created_at, updated_at) VALUES (:jti, NOW(), NOW())";
    $placeholders = ['jti' => $jti];
    $res = $this->run_query($sql, $placeholders)->rowCount();

    if ($res > 0)
    {
      return true;
    }
  }

  public function store($jti, $jwt, $user_id)
  {
    $sql = "INSERT INTO tokens (id, token, created_at, updated_at, users_id) VALUES (:jti, :jwt, NOW(), NOW(), :user_id)";
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

  public function revoke()
  {
    //
  }

  // column 'id' will change to 'jti'
  public function find_by_id($jti)
  {
    $sql = "SELECT * FROM tokens WHERE id =:jti";
    $res = $this->run_query($sql, ['jti' => $jti])->fetch();
    return $res;
  }

  public function find_by_token($jwt)
  {
    $sql = "SELECT * FROM tokens WHERE jwt =:jwt";
    $res = $this->run_query($sql, ['jwt' => $jwt])->fetch();
    return $res;
  }

  public function get_keys($url)
  {
    // this is just a model
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
