<?php

class JWTController
{

  public function generate($user_id)
  {
    $iat = time();
    $exp = $iat + 60 * 120 * 1 * 1; // expiration time set to an hour
    $headers = $this->encode_token_structure([
      'alg' => 'HS256',
      'cty' => 'JWT'
    ]);
    $payload = $this->encode_token_structure([
      'iss' => 'http://ser.local/auth',
      'sub' => 'http://example.local/allowed',
      'iat' => $iat,
      'exp' => $exp,
      'jti' => $this->generate_jti(),
      'id_user' => $user_id
    ]);

    // testing different sign methods

    // $signature = $this->base64_encode_url($this->sign("$headers.$payload", $this->generate_private_key()));
    $signature = $this->base64_encode_url(hash_hmac('SHA256', "$headers.$payload", $this->generate_private_key(), true));
    $token = "$headers.$payload.$signature";
    return $token;
  }

  public function verify()
  {
    //
  }

  public function sign($input, $key)
  {
    openssl_sign($input, $signature, $key, OPENSSL_ALGO_SHA256);
    return $signature;
  }

  // method not working
  public function generate_jti()
  {
    if (!function_exists('uuid_create'))
    {
      return false;
    }

    uuid_create($context);
    uuid_make($context, UUID_MAKE_V4);
    uuid_export($context, UUID_FMT_STR, $uuid);
    return trim($uuid);
  }

  public function generate_private_key()
  {
    $res = openssl_pkey_new([
      'private_key_bits' => 2048,
      'private_key_type' => OPENSSL_KEYTYPE_RSA
    ]);
    openssl_pkey_export($res, $private_key);
    return $private_key;
  }

  public function encode_token_structure($array)
  {
    return $this->base64_encode_url(json_encode($array));
  }

  public function decode_token_structure($array)
  {
    return json_decode(base64_decode($array), true);
  }

  public function base64_encode_url($string)
  {
    return rtrim(strtr(base64_encode($string), '+/', '-_'), '=');
  }

  public function base64_decode_url($string)
  {
    return base64_decode(str_pad(strtr($string, '-_', '+/'), strlen($string) % 4, '=', STR_PAD_RIGHT));
  }
}
