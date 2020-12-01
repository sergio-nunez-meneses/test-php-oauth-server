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

  // jwt controller's method handle_request
  public static function token_request()
  {
    $token = new JWTController();
    $client_credentials = $token->get_token_from_header();

    list($username, $password) = explode(':', base64_decode($client_credentials));
    $user = UserController::check_credentials($username, $password);

    $stored_token = (new JWTModel)->find_by_user($user['id']);

    if ($stored_token)
    {
      if ($token->verify($stored_token['jwt']))
      {
        // redirect user
        $authorization_token = [
          'authorization_token' => $stored_token['jwt'],
          'redirect_uri' => 'http://ser.local/access_token'
        ];

        echo json_encode($authorization_token);
        return;
      }
    }

    $generated_token = $token->generate($user['id']);

    if (empty($generated_token))
    {
      echo "\nToken couldn't be generated.";
      return;
    }

    // return an access token, not an authorization one
    $authorization_token = [
      'authorization_token' => $generated_token,
      'redirect_uri' => 'http://ser.local/access_token'
    ];

    echo json_encode($authorization_token);
    return;
  }

  public static function access_token_request()
  {
    echo (new JWTController)->generate_access_token();
  }

  public static function refresh_token_request()
  {
    echo (new JWTController)->refresh_token();
  }

  public static function revoke_token_request()
  {
    echo (new JWTController)->revoke_token();
  }
}
