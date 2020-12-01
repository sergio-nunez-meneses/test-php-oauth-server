<?php

class CurlController
{
  // methods used for running user_requests_token.php test
  public static function request($token, $url)
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

  // optimize
  public static function get_token($username, $password, $uri, $scope = null)
  {
    // build header and body
    $token = base64_encode("$username:$password");
    $payload = http_build_query([
      'grant_type' => 'client_credentials',
      'scope' => $scope // optional ?
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

    // perform request
    try
    {
      $ch = curl_init($uri);

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

      $response = json_decode($response, true);

      if (!isset($response['authentication_token']))
      {
        throw new Exception('Failed, exiting.');
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

  // change function name
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
        $authentication_token = [
          'authentication_token' => $stored_token['jwt'],
          'redirect_uri' => 'http://ser.local/access_token'
        ];

        echo json_encode($authentication_token);
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
    $authentication_token = [
      'authentication_token' => $generated_token,
      'redirect_uri' => 'http://ser.local/access_token'
    ];

    echo json_encode($authentication_token);
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
