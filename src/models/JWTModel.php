<?php

class JWTModel extends DatabaseModel
{

  public function create($token_type, $jti, $token, $user_id)
  {
    if ($token_type === 'authentication')
    {
      $sql = "INSERT INTO tokens (jti, jwt, created_at, expires_at, users_id) VALUES (:jti, :token, NOW(), NOW() + INTERVAL 1 HOUR, :user_id)";
    }
    elseif ($token_type === 'authorization')
    {
      $sql = "INSERT INTO authorization_tokens (jti, at, created_at, expires_at, users_id) VALUES (:jti, :token, NOW(), NOW() + INTERVAL 10 MINUTE, :user_id)";
    }

    $placeholders = [
      'jti' => $jti,
      'token' => $token,
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
    $sql = "UPDATE tokens SET jti = :new_jti, expires_at = :exp_at WHERE jti = :jti";
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
    // elseif (token_type === 'access_token') "DELETE FROM authorization_tokens"

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
  public function find_by_access_token($authorization_token)
  {
    $sql = "SELECT * FROM authorization_tokens WHERE at =:authorization_token";
    $res = $this->run_query($sql, ['authorization_token' => $authorization_token])->fetch();
    return $res;
  }

  public function find_by_user($user_id)
  {
    $sql = "SELECT * FROM tokens WHERE users_id =:user_id ORDER BY created_at DESC LIMIT 1";
    $res = $this->run_query($sql, ['user_id' => $user_id])->fetch();
    return $res;
  }
}
