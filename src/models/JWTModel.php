<?php

class JWTModel extends DatabaseModel
{

  public function create($jti, $jwt, $user_id)
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

  public function find_by_token($jwt)
  {
    $sql = "SELECT * FROM tokens WHERE jwt =:jwt";
    $res = $this->run_query($sql, ['jwt' => $jwt])->fetch();
    return $res;
  }
}
