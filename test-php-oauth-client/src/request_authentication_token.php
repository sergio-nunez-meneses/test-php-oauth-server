<?php
require 'curl_request.php';

// destroy authentication cookie
if (isset($_COOKIE['authentication_cookie'])) {
  unset($_COOKIE['authentication_cookie']);
  setcookie('authentication_cookie', null, -1, '/');
}

// client: login and request authentication token
$authentication_token = CurlController::request('http://ser.local/auth/request_token', 'sergio', '123456789');

if (empty($authentication_token)) {
  exit("Couldn't generate token.\n");
}

date_default_timezone_set('Europe/Paris');

$exp = time() + (2 * 60 * 1 * 1);
$cookie_name = 'authentication_cookie';
$cookie_value = $authentication_token;

// set cookie and return authentication token
if (setcookie($cookie_name, $cookie_value, $exp, '/')) {
  echo "Authentication token: $authentication_token\n";
  echo 'Cookie expires at: ' . date('m/d/Y H:i:s', $exp) . "\n";
  echo 'Domain name: ' . $_SERVER['HTTP_HOST'] . "\n";
}
