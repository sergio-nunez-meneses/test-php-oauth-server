<?php
require 'curl_request.php';

if (!isset($_COOKIE['authentication_cookie'])) {
  exit("Authentication token not found.\n");
}

// client: request authentication token validation
$encrypted_authorization_token = CurlController::request('http://127.0.0.1:8002/validate', $_COOKIE['authentication_cookie']);

if (!$encrypted_authorization_token) {
  exit("Invalid authentication token.\n");
}

date_default_timezone_set('Europe/Paris');

$exp = time() + (3 * 60 * 1 * 1);
echo 'Your authentication token has been validated. It will expire at ' . date('m/d/Y H:i:s', $exp) . ".\n";
echo "You can now access our services.\n";
echo "Authorization token: $encrypted_authorization_token\n";

// client: is now allowed to access a service
// header('Location: http://127.0.0.1:8002/service?authentication_token=' . $_COOKIE['authentication_cookie']);
