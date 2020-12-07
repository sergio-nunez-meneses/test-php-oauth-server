<?php
require 'curl_request.php';

if (isset($_COOKIE['authentication_cookie'])) {
  // code...
} else {
  // code...
}

// service: request authorization token and authosrize user
$encrypted_authorization_token = CurlController::request('http://ser.local/auth/access_token', $authentication_token);
// var_dump($encrypted_authorization_token); // bug tracker

if (empty($encrypted_authorization_token)) {
  exit("Couldn't find authorization token.");
}

echo "Your authentication token has been validated, you can now access our services.";
echo "Redirecting to http://services.local/service";
