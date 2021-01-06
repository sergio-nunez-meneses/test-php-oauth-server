<?php
require 'curl_request.php';

if (!isset($_COOKIE['authentication_cookie'])) {
  exit("Authentication token not found.\n");
}

$validate_redirection = CurlController::request('http://127.0.0.1:8002/service', $_COOKIE['authentication_cookie']);
// $validate_redirection = json_decode($validate_redirection, true);
//
// if (isset($validate_redirection['error']) && $validate_redirection['error'] === true) {
//   exit($validate_redirection['error_message']);
// }
//
// echo $validate_redirection['success_message'];

// return error and stop script
if (substr($validate_redirection, 0, 1) === '{' || substr($validate_redirection, 0, 1) === '<') {
  echo $validate_redirection;
  exit();
}

echo $validate_redirection;
