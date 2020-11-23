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
        throw new \Exception("Failed to initialize request.");
      }

      curl_setopt_array($ch, $curl_opts);
      $response = curl_exec($ch);
      curl_close($ch);

      var_dump($response);
    }
    catch (\Exception $e)
    {
      trigger_error(
        sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()),
      E_USER_ERROR);
    }
  }

  public static function response_test()
  {
    $headers = apache_request_headers();

    if (array_key_exists('HTTP_AUTHORIZATION', $headers))
    {
      $auth_header = $headers['HTTP_AUTHORIZATION'];
    }
    elseif (array_key_exists('Authorization', $headers))
    {
      $auth_header = $headers['Authorization'];
    }
    else
    {
      echo "\nUnauthorized.";
      return;
    }

    preg_match('/Basic\s(\S+)/', $auth_header, $matches);

    if (!isset($matches[1]))
    {
      echo "\nToken not found.";
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

    echo json_encode([
      'token_type' => 'Bearer',
      'authorization_token' => $generated_token,
      'redirect_uri' => 'http://ser.local/redirected'
    ]);
  }

  public static function redirection_test()
  {
    $headers = apache_request_headers();

    if (array_key_exists('HTTP_AUTHORIZATION', $headers))
    {
      $auth_header = $headers['HTTP_AUTHORIZATION'];
    }
    elseif (array_key_exists('Authorization', $headers))
    {
      $auth_header = $headers['Authorization'];
    }
    else
    {
      echo "\nUnauthorized.";
      return;
    }

    preg_match('/Bearer\s(\S+)/', $auth_header, $matches);

    if (!(new JWTController)->verify($matches[1]))
    {
      echo "\nToken's signature couldn't be verified.";
      return;
    }

    echo "\nYour token has been validated.";
  }
}
