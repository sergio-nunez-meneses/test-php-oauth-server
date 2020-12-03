<?php

class JWTModel extends DatabaseModel
{

  private function table_name($token_type)
  {
    if ($token_type === 'authentication')
    {
      $table = 'tokens';
    }
    elseif ($token_type === 'authorization')
    {
      $table = 'authorization_tokens';
    }
    elseif ($token_type === 'blacklist')
    {
      $table = 'tokens_blacklist';
    }

    return $table;
  }

  public function create($token_type, $jti, $token, $user_id)
  {
    if ($token_type === 'authentication')
    {
      $sql = "INSERT INTO tokens (jti, jwt, created_at, expires_at, users_id) VALUES (:jti, :token, NOW(), NOW() + INTERVAL 2 MINUTE, :user_id)";
    }
    elseif ($token_type === 'authorization')
    {
      $sql = "INSERT INTO authorization_tokens (jti, at, created_at, expires_at, users_id) VALUES (:jti, :token, NOW(), NOW() + INTERVAL 2 MINUTE, :user_id)";
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
  public function read($token_type)
  {
    $table = $this->table_name($token_type);
    $sql = "SELECT * FROM $table";
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

  public function delete($token_type, $jti)
  {
    $table = $this->table_name($token_type);
    $sql = "DELETE FROM $table WHERE jti = :jti";
    $res = $this->run_query($sql, ['jti' => $jti])->rowCount();

    if ($res > 0)
    {
      return true;
    }
  }

  public function add_to_blacklist($jti, $jwt, $at)
  {
    $sql = "INSERT INTO tokens_blacklist (jti, jwt, at) VALUES (:jti, :jwt, :at)";
    $placeholders = [
      'jti' => $jti,
      'jwt' => $jwt,
      'at' => $at
    ];
    $res = $this->run_query($sql, $placeholders)->rowCount();

    if ($res > 0)
    {
      return true;
    }
  }

  // method not tested yet
  public function remove_from_blacklist($jti)
  {
    $sql = "DELETE FROM tokens_blacklist WHERE jti = :jti";
    $res = $this->run_query($sql, ['jti' => $jti])->rowCount();

    if ($res > 0)
    {
      return true;
    }
  }

  public function find_by_jti($token_type, $jti)
  {
    $table = $this->table_name($token_type);
    $sql = "SELECT * FROM $table WHERE jti =:jti";
    $res = $this->run_query($sql, ['jti' => $jti])->fetch();
    return $res;
  }

  public function find_by_token($token_type, $token)
  {
    if ($token_type === 'authentication')
    {
      $sql = "SELECT * FROM tokens WHERE jwt =:token";
    }
    elseif ($token_type === 'authorization')
    {
      $sql = "SELECT * FROM authorization_tokens WHERE at =:token";
    }

    $res = $this->run_query($sql, ['token' => $token])->fetch();
    return $res;
  }

  public function find_by_user($token_type, $user_id)
  {
    $table = $this->table_name($token_type);
    $sql = "SELECT * FROM $table WHERE users_id =:user_id ORDER BY created_at DESC LIMIT 1";
    $res = $this->run_query($sql, ['user_id' => $user_id])->fetch();
    return $res;
  }
}
