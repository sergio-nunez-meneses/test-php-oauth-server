<?php

class CurlController
{
  // methods used for running user_requests_token.php test
  public static function request_test($token, $url)
  {
    $curl_opts = [
      CURLOPT_URL => $url,
      CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        "Authorization: Bearer $token"
      ],
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_VERBOSE => TRUE
    ];

    try
    {
      $ch = curl_init();

      if ($ch === false)
      {
        throw new \Exception('Failed to initialize request.');
      }

      curl_setopt_array($ch, $curl_opts);
      $response = curl_exec($ch);

      if ($response === false)
      {
        throw new Exception(curl_error($ch), curl_errno($ch));
      }

      curl_close($ch);
      return $response;
    }
    catch (\Exception $e)
    {
      trigger_error(
        sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()),
      E_USER_ERROR);
    }
  }

  public static function token_request()
  {
    if (array_key_exists('HTTP_AUTHORIZATION', $_SERVER))
    {
      $authorization_header = $_SERVER['HTTP_AUTHORIZATION'];
    }
    elseif (array_key_exists('Authorization', $_SERVER))
    {
      $authorization_header = $_SERVER['Authorization'];
    }
    else
    {
      echo "\nUnauthorized.";
      return;
    }

    preg_match('/Basic\s(\S+)/', $authorization_header, $matches);

    if (strpos($matches[0], 'Basic'))
    {
      echo "\nInvalid token type.";
      return;
    }

    if (!isset($matches[1]))
    {
      echo "\nClient credentials not found.";
      return;
    }

    list($username, $password) = explode(':', base64_decode($matches[1]));
    $user = UserController::check_credentials($username, $password);
    $token = new JWTController;
    $generated_token = $token->generate($user['id']);

    if (empty($generated_token))
    {
      echo "\nToken couldn't be generated.";
      return;
    }

    $authorization_token = [
      'authorization_token' => $generated_token,
      'redirect_uri' => 'http://ser.local/at_request'
    ];

    echo json_encode($authorization_token);
  }

  public static function access_token_request()
  {
    echo (new JWTController)->generate_access_token();
  }

  public static function refresh_token_request()
  {
    echo (new JWTController)->refresh();
  }

  public static function revoke_token_request()
  {
    echo (new JWTController)->revoke();
  }
}
