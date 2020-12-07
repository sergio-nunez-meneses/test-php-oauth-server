<?php
require 'curl_request.php';

if (!isset($_COOKIE['authentication_cookie'])) {
  exit("Authentication token not found.\n");
}

// service: request authorization token and authosrize user
$encrypted_authorization_token = CurlController::request('http://ser.local/auth/access_token', $_COOKIE['authentication_cookie']);

if (empty($encrypted_authorization_token)) {
  exit("Couldn't find authorization token.\n");
}

$exp = time() + (3 * 60 * 1 * 1);
echo 'Your authentication token has been validated. It will expire at ' . date('m/d/Y H:i:s', $exp) . ".\n";
echo "You can now access our services. You'll be redirected to http://service.local";
