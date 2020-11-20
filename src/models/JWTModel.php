<?php

class JWTModel extends DatabaseModel
{

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

  public function refresh()
  {
    //
  }

  public function revoke()
  {
    //
  }

  public function find_by_id($jti)
  {
    $sql = "SELECT * FROM tokens WHERE id =:jti";
    $res = $this->run_query($sql, ['jti' => $jti])->fetch();
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
