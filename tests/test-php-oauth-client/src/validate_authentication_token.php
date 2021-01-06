<?php
require 'curl_request.php';

if (!isset($_COOKIE['authentication_cookie'])) {
  exit("Authentication token not found.\n");
}

// client: request authentication token validation
$authorization_token = CurlController::request('http://127.0.0.1:8002/validate', $_COOKIE['authentication_cookie']);

// if (!$authorization_token) {
//   exit("Invalid authentication token.\n");
// }

// return error and stop script
if (substr($authorization_token, 0, 1) === '{' || substr($authorization_token, 0, 1) === '<') {
  echo $authorization_token;
  exit();
}

// client: is now allowed to access a service
date_default_timezone_set('Europe/Paris');
$exp = time() + (3 * 60 * 1 * 1);

echo "Authorization token: $authorization_token\n";
echo 'Your authentication token has been validated. It will expire at ' . date('m/d/Y H:i:s', $exp) . ".\n";
echo "You can now access our services.\n";
