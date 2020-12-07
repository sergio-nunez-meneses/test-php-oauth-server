<?php
require 'curl_request.php';

date_default_timezone_set('Europe/Paris');

if (isset($_COOKIE['authentication_cookie'])) {
  unset($_COOKIE['authentication_cookie']);
  setcookie('authentication_cookie', null, -1, '/');
}

// $authentication_token = request($argv[1], $argv[2], $argv[3]);
$authentication_token = CurlController::request('http://ser.local/auth/request_token', 'sergio', '123456789');
// var_dump($authentication_token); // bug tracker

if (empty($authentication_token)) {
  exit("Couldn't generate token.");
}

$exp = time() + (2 * 60 * 1 * 1);
$cookie_name = 'authentication_cookie';
$cookie_value = $authentication_token;
setcookie($cookie_name, $cookie_value, $exp, '/');

echo 'authentication token: ' . $authentication_token . "\n";
echo 'cookie expires at: ' . date('m/d/Y H:i:s', $exp) . "\n";
echo 'domain: ' . $_SERVER['HTTP_HOST'] . "\n";
