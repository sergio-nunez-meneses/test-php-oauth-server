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

  // return error and stop script
  if (substr($encrypted_authorization_token, 0, 1) === '{' || substr($encrypted_authorization_token, 0, 1) === '<') {
    echo $encrypted_authorization_token;
    exit();
  }

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

  $encrypted_authentication_token = $response->get_token_from_header();

  $validate_authentication_token = CurlController::request('http://ser.local/auth/verify_token', $encrypted_authentication_token);

  if (!$validate_authentication_token) {
    $response = [
      'error' => true,
      'error_message' => "Authentication token couldn't be validated.\n"
    ];

    exit(json_encode($response));
  }

  $response = [
    'success_message' => "Welcome back, whatever your name is.\nIf you can see this message, it means that you really have access."
  ];

  echo json_encode($response);
} else {
  exit("Page not found.\n");
}
