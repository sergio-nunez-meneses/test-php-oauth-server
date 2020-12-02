<?php
/*
to run the test, copy and paste the folowing line in the terminal, and replace variables with your user information:
php user_requests_token.php username password uri scope (optional)

user credentials are entered, base64 encoded, and sent to the authorization server for authentication token request
*/

require '../include/class_autoloader.php';
require '../tools/constants.php';

date_default_timezone_set('Europe/Paris');
echo "\n\nRequest started at " . date('H:i:s') . "\n";

// client: login and request authentication token
$token = CurlController::get_token($argv[1], $argv[2], $argv[3]);

if (empty($token)) {
  exit("\n\nCouldn't generate token.\n\n");
}

$authentication_token = $token['authentication_token'];

echo "\n\nYour token has been generated:\n\n";
echo "$authentication_token\n\n";

// service: request authorization token and authorize user
$encrypted_authorization_token = CurlController::request($authentication_token, ISSUER . '/access_token');
var_dump($encrypted_authorization_token);

if (empty($encrypted_authorization_token)) {
  exit("\n\nCouldn't find authorization token.\n");
}

$authorization_token = (new JWTController)->verify_access_token($encrypted_authorization_token);

if (empty($authorization_token)) {
  exit("\n\nInvalid authorization token.\n\n");
}

$user = (new UserModel)->find_by_id($authorization_token['user_id']);

echo "\n\nWelcome, " . ucfirst($user['username']) . "\n\n";
echo "Your authentication token has been validated, you can now access our services.\n\n";
echo "Redirecting to http://services.local/service\n\n";

// service: request refresh token
// $refresh_token = CurlController::request($authentication_token, ISSUER . '/refresh_token');
//
// if (empty($refresh_token)) {
//   exit("\n\nCouldn't refresh token.\n\n");
// }
//
// echo "\n\nYour token has been refreshed, you still have access to our services:\n\n";
// echo "$refresh_token\n\n";

// client: logout and revoke authentication token (since authentication token has been replaced in databse, this doesn't work)
// $logout = CurlController::request($authentication_token, 'http://ser.local/revoke_token');
//
// if ($logout) {
//   echo "\n\nUser logged out.\n\n";
// }

echo "\n\nRequest ended at " . date('H:i:s') . "\n\n";
