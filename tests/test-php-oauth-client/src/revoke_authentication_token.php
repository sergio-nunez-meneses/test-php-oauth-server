<?php
require 'curl_request.php';

if (!isset($_COOKIE['authentication_cookie'])) {
  exit("Authentication token not found.\n");
}

// client: request revoke authentication token
$revoked_tokens = CurlController::request('http://ser.local/auth/revoke_token', $_COOKIE['authentication_cookie']);

// if (!$revoked_authorization_token) {
//   exit("Authentication token couldn't be revoked.\n");
// }

// return error and stop script
if (substr($revoked_tokens, 0, 1) === '{' || substr($revoked_tokens, 0, 1) === '<') {
  echo $revoked_tokens;
  exit();
}

// destroy authentication cookie
if (isset($_COOKIE['authentication_cookie'])) {
  unset($_COOKIE['authentication_cookie']);
  setcookie('authentication_cookie', null, -1, '/');
}

echo "Your authentication token has been revoked.\n";
