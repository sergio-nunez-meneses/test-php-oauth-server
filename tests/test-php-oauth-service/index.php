<?php
require 'src/curl_request.php';

if (array_key_exists('HTTP_AUTHORIZATION', $_SERVER))
{
  $authorization_header = $_SERVER['HTTP_AUTHORIZATION'];
}
elseif (array_key_exists('Authorization', $_SERVER))
{
  $authorization_header = $_SERVER['Authorization'];
}
else
{
  exit("Authentication token not found.\n");
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

if ($uri[1] === 'validate') {
  // service: request authorization token and authorize user
  $encrypted_authentication_token = explode(' ', $authorization_header)[1];
  $encrypted_authorization_token = CurlController::request('http://ser.local/auth/access_token', $encrypted_authentication_token);

  if (empty($encrypted_authorization_token)) {
    exit("Authorization token couldn't be generated.\n");
  }

  echo true;
} else {
  exit("Page not found.\n");
}
