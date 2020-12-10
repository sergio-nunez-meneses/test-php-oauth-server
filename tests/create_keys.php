<?php
// create openssl resource, generate private and public keys, and store them in separate files
$res = openssl_pkey_new([
  'private_key_bits' => 2048,
  'private_key_type' => OPENSSL_KEYTYPE_RSA
]);
openssl_pkey_export($res, $private_key);
$public_key = openssl_pkey_get_details($res);

if (file_put_contents('../keys/private.key', $private_key) && file_put_contents('../keys/public.key', $public_key['key']) && file_put_contents('test-php-oauth-service/keys/private.key', $private_key) && file_put_contents('test-php-oauth-service/keys/public.key', $public_key['key'])) {
  echo "private.key and public.key files created in keys and test-php-oauth-service/keys folders\n";
}
