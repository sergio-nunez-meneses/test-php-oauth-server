<?php
require 'curl_request.php';

// destroy authentication cookie
if (isset($_COOKIE['authentication_cookie'])) {
  unset($_COOKIE['authentication_cookie']);
  setcookie('authentication_cookie', null, -1, '/');
}

// client: login and request authentication token
$client_credentials = filter_var($_POST['client_credentials'], FILTER_SANITIZE_STRING);
$authentication_token = CurlController::request('http://ser.local/auth/request_token', $client_credentials, 'client credentials test');
// $authentication_token = CurlController::request('http://ser.local/auth/request_token', 'juan', '123456789');
// $authentication_token = CurlController::request('https://sergion.promo-41.codeur.online/oauthserver/auth/request_token', 'juan', '123456789');

if (empty($authentication_token)) {
  exit("Authentication token couldn't be generated.\n");
}

date_default_timezone_set('Europe/Paris');

$exp = time() + (3 * 60 * 1 * 1);
$cookie_name = 'authentication_cookie';
$cookie_value = $authentication_token;

// set cookie and return authentication token
if (setcookie($cookie_name, $cookie_value, $exp, '/')) {
  echo "Authentication token: $authentication_token\n";
  echo 'Expires at: ' . date('m/d/Y H:i:s', $exp) . "\n";
}
