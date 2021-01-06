<?php

class CurlController
{

  // methods used for running user_requests_token.php test
  public static function request()
  {
    $num_args = func_num_args();
    $args = func_get_args();

    if ($num_args === 3)
    {
      // build header and body
      $token = base64_encode($args[1] . ':' . $args[2]);
      $payload = http_build_query([
        'grant_type' => 'client_credentials',
        'scope' => '' // optional ?
      ]);

      $curl_opts = [
        CURLOPT_HTTPHEADER => [
          'Content-Type: application/x-www-form-urlencoded',
          "Authorization: Basic $token",
        ],
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false, // fixed bug 'curl: (60) SSL certificate problem: unable to get local issuer certificate'
        CURLOPT_SSL_VERIFYHOST => false, // 'curl: (51) SSL peer certificate or SSH remote key was not OK'
        CURLOPT_SSL_VERIFYSTATUS => false,
        CURLOPT_VERBOSE => TRUE
      ];
    }
    elseif ($num_args === 2)
    {
      $curl_opts = [
        CURLOPT_HTTPHEADER => [
          'Content-Type: application/json',
          'Authorization: Bearer ' . $args[1]
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYSTATUS => false,
        CURLOPT_VERBOSE => TRUE
      ];
    }
    else
    {
      return self::error_handler('Invalid request.');
    }

    $url = filter_var($args[0], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED);

    return CurlController::execute_request($curl_opts, $url);
  }

  private static function execute_request($curl_opts, $url)
  {
    $ch = curl_init($url);

    if ($ch === false)
    {
      return self::error_handler('Failed to initialize request.');
    }

    curl_setopt_array($ch, $curl_opts);
    $response = curl_exec($ch); // process request and return response

    if ($response === false)
    {
      return self::error_handler('Curl error ' . curl_errno($ch) . ': ' . curl_strerror(curl_errno($ch)));
    }

    curl_close($ch);
    return $response;
  }

  // return authentication token
  public static function token_request()
  {
    $token = new JWTController();
    $client_credentials = $token->get_token_from_header();

    if (!is_array($client_credentials))
    {
      echo self::error_handler($client_credentials);
      return;
    }

    list($username, $password) = explode(':', base64_decode($client_credentials['response_value']));
    $user = UserController::verify($username, $password);

    if (!is_array($user))
    {
      echo self::error_handler($user);
      return;
    }

    $stored_token = (new JWTModel)->find_by_user('authentication', $user['id']);

    if ($stored_token)
    {
      $verified_token = $token->verify($stored_token['token']);

      if (!is_array($verified_token))
      {
        echo self::error_handler($verified_token);
        return;
      }

      echo $stored_token['token'];
      return;
    }

    $generated_token = $token->generate($user['id']);

    if (!is_array($generated_token))
    {
      echo self::error_handler($generated_token);
    }

    echo $generated_token['response_value'];
    return;
  }

  public static function verify_token_request()
  {
    $verified_token = (new JWTController)->verify();

    if (!is_array($verified_token))
    {
      echo self::error_handler($verified_token);
      return;
    }

    echo $verified_token['response_value'];
    return;
  }

  // return authorization token
  public static function access_token_request()
  {
    $token = new JWTController();
    $authentication_token = $token->get_token_from_header();

    if (!is_array($authentication_token))
    {
      echo self::error_handler($authentication_token);
      return;
    }

    $authentication_token = $authentication_token['response_value'];

    $stored_authorization_token = (new JWTModel)->find_by_jti('authorization', $authentication_token['jti']);

    if ($stored_authorization_token)
    {
      $verified_authorization_token = $token->verify_access_token($stored_authorization_token['token']);

      if (!is_array($verified_authorization_token))
      {
        echo self::error_handler($verified_authorization_token);
        return;
      }

      echo $stored_authorization_token['token'];
      return;
    }

    $generated_token = $token->generate_access_token($authentication_token['jti'], $authentication_token['users_id']);

    if (!is_array($generated_token))
    {
      echo self::error_handler($generated_token);
    }

    echo $generated_token['response_value'];
    return;
  }

  public static function verify_access_token_request()
  {
    echo (new JWTController)->verify_access_token();
  }

  public static function refresh_token_request()
  {
    echo (new JWTController)->refresh_token();
  }

  public static function revoke_token_request()
  {
    $revoked_tokens = (new JWTController)->revoke_token();

    if ($revoked_tokens !== true)
    {
      echo self::error_handler($revoked_tokens);
      return;
    }

    echo $revoked_tokens;
    return;
  }

  private static function error_handler($value)
  {
    $response = [
      'response_type' => 'error',
      'response_value' => $value
    ];

    return json_encode($response);
  }
}
