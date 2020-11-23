<?php
// to run the test, copy and paste the folowing line in the terminal, and replace variable with an user id:
// php generate_and_verify_jwt.php user_id

require_once('../include/class_autoloader.php');

$token = new JWTController();
$new_token = $token->generate($argv[1]);
echo "Token: \n\n$new_token";

if ($token->verify($new_token)) {
  echo "\n\nYou have a valid token.";
}
