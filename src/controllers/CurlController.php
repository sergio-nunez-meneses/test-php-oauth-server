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
        CURLOPT_SSL_VERIFYPEER => false, // fixed bug 'Curl failed with error #60'
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
        CURLOPT_VERBOSE => TRUE
      ];
    }
    else
    {
      throw new \Exception('Invalid request.');
    }

    $url = filter_var($args[0], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED);

    return CurlController::execute_request($curl_opts, $url);
  }

  private static function execute_request($curl_opts, $url)
  {
    try
    {
      $ch = curl_init($url);

      if ($ch === false)
      {
        throw new \Exception('Failed to initialize request.');
      }

      curl_setopt_array($ch, $curl_opts);
      $response = curl_exec($ch); // process request and return response

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

  // return authentication token
  public static function token_request()
  {
    $token = new JWTController();
    $client_credentials = $token->get_token_from_header();

    list($username, $password) = explode(':', base64_decode($client_credentials));
    $user = UserController::check_credentials($username, $password);

    $stored_token = (new JWTModel)->find_by_user('authentication', $user['id']);

    if ($stored_token)
    {
      if ($token->verify($stored_token['token']))
      {
        echo $stored_token['token'];
        return;
      }
      else
      {
        echo 'Token revoked.';
        return;
      }
    }

    $generated_token = $token->generate($user['id']);

    if (empty($generated_token))
    {
      echo "\nToken couldn't be generated.";
      return;
    }

    echo $generated_token;
    return;
  }

  public static function verify_token_request()
  {
    echo (new JWTController)->verify();
  }

  // return authorization token
  public static function access_token_request()
  {
    $token = new JWTController();
    $authentication_token = $token->get_token_from_header();

    $stored_authorization_token = (new JWTModel)->find_by_jti('authorization', $authentication_token['jti']);

    if ($stored_authorization_token)
    {
      if ($token->verify_access_token($stored_authorization_token['token']))
      {
        echo $stored_authorization_token['token'];
        return;
      }
      else
      {
        echo 'Token revoked.';
        return;
      }
    }

    $generated_token = $token->generate_access_token($authentication_token['jti'], $authentication_token['users_id']);

    if (empty($generated_token))
    {
      echo "\nToken couldn't be generated.";
      return;
    }

    echo $generated_token;
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
    echo (new JWTController)->revoke_token();
  }
}
