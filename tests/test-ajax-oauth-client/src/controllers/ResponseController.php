<?php

class ResponseController
{

  public static function request_token($inputs)
  {
    // destroy authentication cookie
    if (isset($_COOKIE['authentication_cookie']))
    {
      self::delete_cookie($$_COOKIE['authentication_cookie']);
    }

    // client: login and request authentication token
    $client_credentials = filter_var($inputs['client_credentials'], FILTER_SANITIZE_STRING);
    $authentication_token = RequestController::request('http://ser.local/auth/request_token', $client_credentials, 'client_credentials');
    // $authentication_token = CurlController::request('https://sergion.promo-41.codeur.online/oauthserver/auth/request_token', $client_credentials, 'client_credentials');
    // $authentication_token = CurlController::request('https://auth.davi.fr/auth/request_token', $client_credentials, 'client_credentials');

    // return error and stop script
    $error = self::get_error($authentication_token);

    if ($error)
    {
      return $error;
    }

    date_default_timezone_set('Europe/Paris');
    $exp = time() + (3 * 60 * 1 * 1);
    $cookie_name = 'authentication_cookie';
    $cookie_value = $authentication_token;

    // set cookie and return authentication token
    if (setcookie($cookie_name, $cookie_value, $exp, '/'))
    {
      return self::response_handler('authenticated', $authentication_token);
    }
  }

  public static function validate_token()
  {
    if (!isset($_COOKIE['authentication_cookie']))
    {
      return 'Authentication token not found.';
    }

    // client: request authentication token validation
    $authorization_token = RequestController::request('http://service.local/validate', $_COOKIE['authentication_cookie']);

    // return error and stop script
    $error = self::get_error($authorization_token);

    if ($error)
    {
      return $error;
    }

    return self::response_handler('validated', $authorization_token);
  }

  private static function delete_cookie($cookie)
  {
    unset($cookie);
    setcookie('authentication_cookie', null, -1, '/');
  }

  private static function get_error($token) {
    if (substr($token, 0, 1) === '{' || substr($token, 0, 1) === '<') {
      return true;
    }
  }

  private static function response_handler($type, $value)
  {
    $response = [
      'type' => $type,
      'authorization_token' => $value,
    ];

    return json_encode($response);
  }
}
