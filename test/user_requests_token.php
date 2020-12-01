<?php
/*
to run the test, copy and paste the folowing line in the terminal, and replace variables with your user information:
php user_requests_token.php username password uri scope (optional)

user credentials are entered, base64 encoded, and sent to the authorization server for authorization token request
*/

require_once('../include/class_autoloader.php');

echo "\n\nRequest started at " . date('H:i:s') . "\n";

// login, request and validate token
$token = get_token($argv[1], $argv[2], $argv[3], $argv[4]);

if (empty($token)) {
  echo "\n\nCouldn't generate token.\n";
}

echo "\n\n" . $token['authorization_token'];
echo "\n\nYour token has been generated.\n\n";

$access_token = CurlController::request_test($token['authorization_token'], $token['redirect_uri']);
$access_token = json_decode($access_token, true);

if (empty($access_token)) {
  echo "\n\nUnauthorized.\n";
}

echo "\n\n" . $access_token['access_token'];
echo "\n\n" . $access_token['user_id'];
echo "\n\nYour token has been validated, you can now access our services.";
echo "\nRedirecting to http://services.local/service\n\n";

// request refresh token
$refresh_token = CurlController::request_test($access_token['access_token'], 'http://ser.local/refresh_token');

if (empty($refresh_token)) {
  echo "\n\nUnauthorized.\n";
}

echo "\n\n$refresh_token";
echo "\n\nYour token has been refreshed, you still have access to our services.\n\n";

// logout and revoke token (since token has been replaced, this doesn't work)
$logout = CurlController::request_test($access_token, 'http://ser.local/revoke_token');

if ($logout) {
  echo "\n\nUser logged out.\n\n";
}

echo "\n\nRequest ended at " . date('H:i:s') . "\n\n";

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
      "Authorization: Basic $token",
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

    if (!isset($response['authorization_token'])) {
      throw new Exception('Failed, exiting.');
    }

    curl_close($ch);
    return $response;
  } catch (\Exception $e) {
    trigger_error(
      sprintf('Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()),
    E_USER_ERROR);
  }
}
