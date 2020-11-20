<?php

class JWTController
{

  public function generate($user_id)
  {
    $jti = $this->generate_jti();
    $iat = time();
    $exp = $iat + 60 * 120 * 1 * 1; // expiration time set to an hour
    $headers = $this->encode_token_structure([
      'alg' => 'HS256',
      'cty' => 'JWT'
    ]);
    $payload = $this->encode_token_structure([
      'iss' => 'http://ser.local',
      'sub' => 'http://example.local/allowed',
      'iat' => $iat,
      'exp' => $exp,
      'jti' => $jti,
      'id_user' => $user_id
    ]);

    // testing different sign methods
    $signature = $this->base64_encode_url($this->sign("$headers.$payload", $this->generate_private_key()));
    // $signature = $this->base64_encode_url(hash_hmac('SHA256', "$headers.$payload", $this->generate_private_key(), true));
    $token = "$headers.$payload.$signature";
    $store_jti = new JWTModel;

    if (!$store_jti->store_id($jti))
    {
      throw new \Exception('Failed to store token id.');
    }

    return $token;
  }

  public function verify($token)
  {
    if (!isset($token))
    {
      throw new \Exception('Token not found.');
    }

    if (!stristr($token, '.'))
    {
      throw new \Exception("Token doesn't contain expected delimiter.");
    }

    if (count(explode('.', $token)) !== 3)
    {
      throw new \Exception("Token doesn't contain expected structure.");
    }

    // deconstruct and decode token structure
    list($header, $payload, $signature) = explode('.', $token);
    $decoded_header = $this->decode_token_structure($header);
    $decoded_payload = $this->decode_token_structure($payload);

    if ($decoded_payload['iat'] > time())
    {
      throw new \Exception('Token was issued in the future (well played Jonas Kahnwald).');
    }

    if ($decoded_payload['exp'] < time())
    {
      throw new \Exception('Token expired.');
    }

    if ($decoded_payload['iss'] !== 'http://ser.local')
    {
      throw new \Exception("Token doesn't contain expected issuer.");
    }

    // $url = $decoded_payload['iss'] . '/.well-known/oauth-authorization-server';
    // $keys = (new TokenModel)->get_keys($url);

    $stored_token = new JWTModel;

    if (!$stored_token->find_by_id($decoded_payload['jti']))
    {
      throw new \Exception("Invalid token id.");
    }

    // create public key from resource
    $private_key = file_get_contents('../keys/private.key');
    $res = openssl_pkey_get_private($private_key);

    if (!$res)
    {
      throw new \Exception("Invalid public key.");
    }

    $public_key = openssl_pkey_get_details($res);

    if (openssl_verify("$header.$payload", $this->base64_decode_url($signature), $public_key['key'], OPENSSL_ALGO_SHA256))
    {
      return true;
    }
    else
    {
      throw new \Exception("Token's signature couldn't be verified.");
      return false;
    }
  }

  protected function generate_jti()
  {
    // method not working
    // if (!function_exists('uuid_create'))
    // {
    //   return false;
    // }
    //
    // uuid_create($context);
    // uuid_make($context, UUID_MAKE_V4);
    // uuid_export($context, UUID_FMT_STR, $uuid);
    // return trim($uuid);

    return uniqid('', true);
  }

  protected function generate_private_key()
  {
    // create openssl resource and save private key in a file
    // $res = openssl_pkey_new([
    //   'private_key_bits' => 2048,
    //   'private_key_type' => OPENSSL_KEYTYPE_RSA
    // ]);
    // openssl_pkey_export($res, $private_key);
    // file_put_contents('../keys/private.key', $private_key);

    $private_key = file_get_contents('../keys/private.key');

    if ($private_key)
    {
      return $private_key;
    }
  }

  protected function sign($input, $key)
  {
    if (openssl_sign($input, $signature, $key, OPENSSL_ALGO_SHA256))
    {
      return $signature;
    }
  }

  protected function encode_token_structure($array)
  {
    return $this->base64_encode_url(json_encode($array));
  }

  protected function decode_token_structure($array)
  {
    return json_decode(base64_decode($array), true);
  }

  protected function base64_encode_url($string)
  {
    return rtrim(strtr(base64_encode($string), '+/', '-_'), '=');
  }

  protected function base64_decode_url($string)
  {
    return base64_decode(str_pad(strtr($string, '-_', '+/'), strlen($string) % 4, '=', STR_PAD_RIGHT));
  }

  public static function curl_response_test()
  {
    $headers = apache_request_headers();

    if (array_key_exists('HTTP_AUTHORIZATION', $headers))
    {
      $auth_header = $headers['HTTP_AUTHORIZATION'];
    }
    elseif (array_key_exists('Authorization', $headers))
    {
      $auth_header = $headers['Authorization'];
    }
    else
    {
      echo "\nUnauthorized.";
      return;
    }

    preg_match('/Basic\s(\S+)/', $auth_header, $matches);

    if (!isset($matches[1]))
    {
      echo "\nToken not found.";
      return;
    }

    list($username, $password) = explode(':', base64_decode($matches[1]));
    // $user_id = $user_model->get_id($inputs['license']);
    $user = UserController::check_credentials($username, $password);
    $token = new JWTController;
    $generated_token = $token->generate($user['id']);

    if (empty($generated_token))
    {
      "\nToken couldn't be generated.";
      return;
    }

    echo json_encode([
      'token_type' => 'JWT',
      'authorization_token' => $generated_token,
      'redirect_uri' => 'http://ser.local/redirected'
    ]);
  }

  public static function curl_redirection_test()
  {
    $headers = apache_request_headers();

    if (array_key_exists('HTTP_AUTHORIZATION', $headers))
    {
      $auth_header = $headers['HTTP_AUTHORIZATION'];
    }
    elseif (array_key_exists('Authorization', $headers))
    {
      $auth_header = $headers['Authorization'];
    }
    else
    {
      echo "\nUnauthorized.";
      return;
    }

    preg_match('/Bearer\s(\S+)/', $auth_header, $matches);

    if (!(new JWTController)->verify($matches[1]))
    {
      echo "\nToken's signature couldn't be verified.";
      return;
    }

    echo "\nYour token has been validated.";
  }
}
