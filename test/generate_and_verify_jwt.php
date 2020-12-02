<?php
// to run the test, copy and paste the folowing line in the terminal, and replace variable with an user id:
// php generate_and_verify_jwt.php user_id

require '../include/class_autoloader.php';

$token = new JWTController();
$new_token = $token->generate($argv[1]);

// print results
echo "Encrypted token: \n$new_token";
echo "\n\nDecrypted token: \n" . $token->decrypt_token($new_token);

// since token is not get from header, this throws an error
if ($token->verify($new_token)) {
  echo "\n\nYou have a valid token.";
}
