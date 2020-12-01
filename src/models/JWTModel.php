<?php

class JWTModel extends DatabaseModel
{

  public function create($jti, $jwt, $user_id)
  {
    // if (token_type === 'jwt') "INSERT INTO tokens"
    // elseif (token_type === 'access_token') "INSERT INTO access_tokens"

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

  // method not tested yet
  public function read()
  {
    $sql = "SELECT * FROM tokens";
    $res = $this->run_query($sql);

    if ($res->rowCount() > 0)
    {
      return $res;
    }
  }

  // method not tested yet
  public function update($jti, $new_jti)
  {
    $sql = "UPDATE tokens SET jti = :new_jti, updated_at = NOW() WHERE jti = :jti";
    $placeholders = [
      'jti' => $jti,
      'jti' => $new_jti
    ];
    $res = $this->run_query($sql, $placeholders)->rowCount();

    if ($res > 0)
    {
      return true;
    }
  }

  public function delete($jti)
  {
    // if (token_type === 'jwt') "DELETE FROM tokens"
    // elseif (token_type === 'access_token') "DELETE FROM access_tokens"

    $sql = "DELETE FROM tokens WHERE jti = :jti";
    $res = $this->run_query($sql, ['jti' => $jti])->rowCount();

    if ($res > 0)
    {
      return true;
    }
  }

  public function find_by_jti($jti)
  {
    $sql = "SELECT * FROM tokens WHERE jti =:jti";
    $res = $this->run_query($sql, ['jti' => $jti])->fetch();
    return $res;
  }

  public function find_by_jwt($jwt)
  {
    $sql = "SELECT * FROM tokens WHERE jwt =:jwt";
    $res = $this->run_query($sql, ['jwt' => $jwt])->fetch();
    return $res;
  }

  // method not tested yet
  public function find_by_access_token($access_token)
  {
    $sql = "SELECT * FROM access_tokens WHERE access_token =:access_token";
    $res = $this->run_query($sql, ['access_token' => $access_token])->fetch();
    return $res;
  }

  public function find_by_user($user_id)
  {
    $sql = "SELECT * FROM tokens WHERE users_id =:user_id ORDER BY created_at DESC LIMIT 1";
    $res = $this->run_query($sql, ['user_id' => $user_id])->fetch();
    return $res;
  }
}
