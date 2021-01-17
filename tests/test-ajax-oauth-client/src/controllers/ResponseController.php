<?php

class ResponseController
{

  public static function request_token($inputs)
  {
    // destroy authentication cookie
    if (isset($_COOKIE['authentication_cookie'])) {
      unset($_COOKIE['authentication_cookie']);
      setcookie('authentication_cookie', null, -1, '/');
    }

    // client: login and request authentication token
    $client_credentials = filter_var($inputs['client_credentials'], FILTER_SANITIZE_STRING);
    $authentication_token = RequestController::request('http://ser.local/auth/request_token', $client_credentials, 'client_credentials');
    // $authentication_token = CurlController::request('https://sergion.promo-41.codeur.online/oauthserver/auth/request_token', $client_credentials, 'client_credentials');
    // $authentication_token = CurlController::request('https://auth.davi.fr/auth/request_token', $client_credentials, 'client_credentials');

    // return error and stop script
    if (substr($authentication_token, 0, 1) === '{' || substr($authentication_token, 0, 1) === '<') {
      return $authentication_token;
    }

    date_default_timezone_set('Europe/Paris');
    $exp = time() + (3 * 60 * 1 * 1);
    $cookie_name = 'authentication_cookie';
    $cookie_value = $authentication_token;

    // set cookie and return authentication token
    if (setcookie($cookie_name, $cookie_value, $exp, '/')) {
      $response = [
        'type' => 'authenticated',
        'authentication_token' => $authentication_token
      ];

      return json_encode($response);
    }
  }

  public static function validate_token()
  {
    if (!isset($_COOKIE['authentication_cookie'])) {
      exit("Authentication token not found.\n");
    }

    // client: request authentication token validation
    $authorization_token = RequestController::request('http://service.local/validate', $_COOKIE['authentication_cookie']);

    // return error and stop script
    if (substr($authorization_token, 0, 1) === '{' || substr($authorization_token, 0, 1) === '<') {
      return $authentication_token;
    }
    $response = [
      'type' => 'validated',
      'authorization_token' => $authorization_token,
    ];

    return json_encode($response);
  }
}
