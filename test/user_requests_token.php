<?php
// to run the test, paste the folowing line in the terminal and replace variables with your user information:
// php user_requests_token.php username, password, uri, scope

require_once('../include/class_autoloader.php');

get_token($argv[1], $argv[2], $argv[3], $argv[4]);

// user credentials are entered, base64 encoded, and sent to the authorization server for authorization token request
function get_token($username, $password, $uri, $scope = null) {
  // build header and body
  $token = base64_encode("$username:$password");
  $payload = http_build_query([
    'grant_type' => 'client_credentials',
    'scope' => $scope // optional ?
  ]);
  $curl_opts = [
    CURLOPT_HTTPHEADER => [
      'Content-Type: application/x-www-form-urlencoded',
      "Authorization: Basic $token"
    ],
    CURLOPT_POST => 1,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false, // fixed bug 'Curl failed with error #60'
    CURLOPT_VERBOSE => TRUE
  ];

  // perform request
  try {
    $ch = curl_init($uri);

    if ($ch === false) {
      throw new \Exception('Failed to initialize request.');
    }

    curl_setopt_array($ch, $curl_opts);
    $response = curl_exec($ch); // process request and return response

    if ($response === false) {
      throw new Exception(curl_error($ch), curl_errno($ch));
    }

    $response = json_decode($response, true);

    if (!isset($response['authorization_token']) || !isset($response['token_type'])) {
      throw new Exception('Failed, exiting.');
    }

    curl_close($ch);
    CurlController::request_test($response['authorization_token'], $response['redirect_uri']);
  } catch (\Exception $e) {
    trigger_error(
      sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()),
    E_USER_ERROR);
  }
}
