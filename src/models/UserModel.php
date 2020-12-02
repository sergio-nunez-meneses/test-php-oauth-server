<?php

class UserModel extends DatabaseModel
{

  public function find_by_id($id)
  {
    $sql = "SELECT * FROM users WHERE id =:id";
    $res = $this->run_query($sql, ['id' => $id])->fetch();
    return $res;
  }

  public function find_by_name($username)
  {
    $sql = "SELECT * FROM users WHERE username =:username";
    $res = $this->run_query($sql, ['username' => $username])->fetch();
    return $res;
  }

  public function get_id($license)
  {
    // decrypt license number to get user id
    return $user_id;
  }
}
