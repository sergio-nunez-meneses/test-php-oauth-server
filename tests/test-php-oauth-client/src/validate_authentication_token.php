<?php
require 'curl_request.php';

if (!isset($_COOKIE['authentication_cookie'])) {
  exit("Authentication token not found.\n");
}

// service: request authorization token and authorize user
$encrypted_authorization_token = CurlController::request('http://service.local/validate', $_COOKIE['authentication_cookie']);

if (!$encrypted_authorization_token) {
  exit("Invalid authentication token.\n");
}

date_default_timezone_set('Europe/Paris');

$exp = time() + (3 * 60 * 1 * 1);
echo 'Your authentication token has been validated. It will expire at ' . date('m/d/Y H:i:s', $exp) . ".\n";
echo "You can now access our services.\n";
echo "Authorization token: $encrypted_authorization_token\n";
