<?php
require_once('../include/class_autoloader.php');

// handle cross origin resource sharing (CORS)
header('Access-Control-Allow-Origin: *'); // allow all requests from all origins
header('Access-Control-Allow-Methods: OPTIONS, GET, POST'); // allow only specified request methods
header('Access-Control-Max-Age: 3600'); // add max time
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri)[1];
$valid_uris = ['', 't_request', 'at_request', 'rt_request'];

if (in_array($uri, $valid_uris)) {
  if ($uri === '' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    UserController::login_form();
  } elseif ($uri === 't_request' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    CurlController::token_request();
  } elseif ($uri === 'at_request' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    CurlController::access_token_request();
  } elseif ($uri === 'rt_request' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    CurlController::revoke_token_request();
  }
} else {
  throw new \Exception('Page ' . strtoupper($uri) . ': Not found.');
}
