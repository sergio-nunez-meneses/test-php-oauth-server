<?php

class UserController
{

  // method used for running user_requests_token.php test
  public static function verify($username, $password)
  {
    $error = false;
    $error_message = '';
    $username = filter_var($username, FILTER_SANITIZE_STRING);
    $password = filter_var($password, FILTER_DEFAULT);

    if (empty($username))
    {
      $error = true;
      $error_message .= "Username can't be empty ";
    }

    if (empty($password))
    {
      $error = true;
      $error_message .= "Password can't be empty";
    }

    if ($error)
    {
      return $error_message;
    }

    $user = (new UserModel)->find_by_name($username);

    if (empty($user))
    {
      return "User doesn't exist";
    }

    $stored_password = $user['password'];

    if (!password_verify($password, $stored_password))
    {
      return "Passwords don't match.";
    }

    return $user;
  }

  public static function logout()
  {
    return (new JWTController)->revoke();
  }
}
