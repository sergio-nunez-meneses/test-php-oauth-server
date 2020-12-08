<?php
require '../include/class_autoloader.php';

// handle cross origin resource sharing (CORS)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: OPTIONS, GET, POST'); // allow only specified request methods
header('Access-Control-Max-Age: 3600'); // add max time ?
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

IndexController::route_requests($uri, $_SERVER['REQUEST_METHOD']);
