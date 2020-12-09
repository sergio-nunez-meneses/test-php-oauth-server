<?php
require 'src/curl_request.php';
require 'src/response.php';

// handle cross origin resource sharing (CORS)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: OPTIONS, GET, POST');
header('Access-Control-Max-Age: 3600');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

if ($uri[1] === 'validate') {
  // service: request authorization token and authorize user
  $response = new ReponseController();

  if (!$response->get_origin_from_header()) {
    exit("403: Unauthorized.");
  }

  $encrypted_authentication_token = $response->get_token_from_header();
  $encrypted_authorization_token = CurlController::request('http://ser.local/auth/access_token', $encrypted_authentication_token);
  $validate_authorization_token = $response->verify_authorization_token($encrypted_authorization_token);

  if (!$validate_authorization_token) {
    exit("Authorization token couldn't be validated.\n");
  }

  echo $encrypted_authorization_token;
} elseif ($uri[1] === 'service') {
  // client: access service
  $response = new ReponseController();

  if (!$response->get_origin_from_header()) {
    exit("403: Unauthorized.\n");
  }

  if (!isset($_GET['authentication_token'])) {
    exit("Authentication token not found.\n");
  }

  $validate_authentication_token = CurlController::request('http://ser.local/auth/verify_token', $_GET['authentication_token']);

  if (!$validate_authentication_token) {
    exit("Authentication token couldn't be validated.\n");
  }

  echo "Welcome back, whatever your name is.\nIf you can see this message, it means that you really have access.";
} else {
  exit("Page not found.\n");
}
