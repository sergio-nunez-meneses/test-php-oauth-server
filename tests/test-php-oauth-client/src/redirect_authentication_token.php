<?php
require 'curl_request.php';

if (!isset($_COOKIE['authentication_cookie'])) {
  exit("Authentication token not found.\n");
}

$validate_redirection = CurlController::request('http://127.0.0.1:8002/service', $_COOKIE['authentication_cookie']);

// return error and stop script
if (substr($validate_redirection, 0, 1) === '{' || substr($validate_redirection, 0, 1) === '<') {
  echo $validate_redirection;
  exit();
}

// client: now access a service
echo $validate_redirection;
