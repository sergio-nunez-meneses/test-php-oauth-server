<?php

class UserController
{

  public static function login_form()
  {
    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
      if (isset($_POST['login']))
      {
        UserController::login($_POST);
      }
    }
    UserView::display();
  }

  public static function login($inputs)
  {
    $error = false;
    $success_msg = $error_msg = '';

    if (empty($inputs['username']))
    {
      $error = true;
      $error_msg .= "Username can't be empty <br>";
    }

    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);

    if (empty($inputs['password']))
    {
      $error = true;
      $error_msg .= "Password can't be empty <br>";
    }

    $password = filter_input(INPUT_POST, 'password', FILTER_DEFAULT);

    if ($error)
    {
      echo $error_msg;
    }

    $user_model = new UserModel;
    $user = $user_model->find_by_name($username);

    if (empty($user))
    {
      $error_msg .= "User doesn't exist <br>";
      echo $error_msg;
    }

    $stored_password = $user['password'];

    if (!password_verify($password, $stored_password))
    {
      $error_msg .= "Passwords don't match <br>";
      echo $error_msg;
    }

    // $user_id = $user_model->get_id($inputs['license']);
    // generate JWT and store it as 'HTTP_AUTHORIZATION' or 'Authorization' HTTP header
    $token = (new JWTController)->generate($user['id']);

    if (empty($token))
    {
      $error_msg .= "Token couldn't be generated <br>";
    }

    $success_msg .= 'Connection success! <br>';
    echo "token: <br>$token<br>";
    echo $success_msg;
  }
}
