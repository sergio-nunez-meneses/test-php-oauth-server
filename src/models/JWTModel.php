<?php

class JWTModel extends DatabaseModel
{

  private function get_table_name($keyword)
  {
    if ($keyword === 'authentication')
    {
      $table_name = 'authentication_tokens';
    }
    elseif ($keyword === 'authorization')
    {
      $table_name = 'authorization_tokens';
    }
    elseif ($keyword === 'blacklist')
    {
      $table_name = 'tokens_blacklist';
    }

    return $table_name;
  }

  public function create($token_type, $jti, $token, $user_id)
  {
    if ($token_type === 'authentication')
    {
      $sql = "INSERT INTO authentication_tokens (jti, token, created_at, expires_at, users_id) VALUES (:jti, :token, NOW(), NOW() + INTERVAL 3 MINUTE, :user_id)";
    }
    elseif ($token_type === 'authorization')
    {
      $sql = "INSERT INTO authorization_tokens (jti, token, created_at, expires_at, users_id) VALUES (:jti, :token, NOW(), NOW() + INTERVAL 2 MINUTE, :user_id)";
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
  public function read($keyword)
  {
    $table_name = $this->get_table_name($keyword);
    $sql = "SELECT * FROM $table_name";
    $res = $this->run_query($sql);

    if ($res->rowCount() > 0)
    {
      return $res;
    }
  }

  // method not tested yet
  public function update($jti, $new_jti)
  {
    $table_name = $this->get_table_name($keyword);
    $sql = "UPDATE $table_name SET jti = :new_jti WHERE jti = :jti";
    $placeholders = [
      'jti' => $new_jti,
      'jti' => $jti
    ];
    $res = $this->run_query($sql, $placeholders)->rowCount();

    if ($res > 0)
    {
      return true;
    }
  }

  public function delete($keyword, $jti)
  {
    $table_name = $this->get_table_name($keyword);
    $sql = "DELETE FROM $table_name WHERE jti = :jti";
    $res = $this->run_query($sql, ['jti' => $jti])->rowCount();

    if ($res > 0)
    {
      return true;
    }
  }

  public function add_to_blacklist($jti, $token, $token_type, $user_id)
  {
    $sql = "INSERT INTO tokens_blacklist (jti, token, token_type, users_id) VALUES (:jti, :token, :token_type, :user_id)";
    $placeholders = [
      'jti' => $jti,
      'token' => $token,
      'token_type' => $token_type,
      'user_id' => $user_id
    ];
    $res = $this->run_query($sql, $placeholders)->rowCount();

    if ($res > 0)
    {
      return true;
    }
  }

  public function find_by_jti($keyword, $jti)
  {
    $table_name = $this->get_table_name($keyword);
    $sql = "SELECT * FROM $table_name WHERE jti =:jti";
    $res = $this->run_query($sql, ['jti' => $jti])->fetch();
    return $res;
  }

  public function find_by_token($keyword, $token)
  {
    $table_name = $this->get_table_name($keyword);
    $sql = "SELECT * FROM $table_name WHERE token =:token";
    $res = $this->run_query($sql, ['token' => $token])->fetch();
    return $res;
  }

  public function find_by_user($keyword, $user_id)
  {
    $table_name = $this->get_table_name($keyword);

    if ($table_name === 'authentication' || $table_name === 'authorization')
    {
      $sql = "SELECT * FROM $table_name WHERE users_id =:user_id ORDER BY created_at DESC LIMIT 1";
    }
    else
    {
      $sql = "SELECT * FROM $table_name WHERE users_id =:user_id";
    }

    $res = $this->run_query($sql, ['user_id' => $user_id])->fetch();
    return $res;
  }
}
