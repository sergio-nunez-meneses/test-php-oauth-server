<?php
require_once('../include/class_autoloader.php');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

if ($uri[1] === '') {
  UserController::login_form();
} elseif ($uri[1] === 'token') {
  JWTController::curl_response_test();
} else {
  throw new \Exception('Page ' . $uri[1] . ': Not found.');
}
