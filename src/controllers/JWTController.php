<?php

class JWTController
{

  public function generate($user_id, $algorithm = 'HS256')
  {
    $headers = $this->generate_header($algorithm);
    $payload = $this->generate_payload($user_id, 'some_scope');
    $token = [
      $this->encode_token_structure($headers),
      $this->encode_token_structure($payload)
    ];
    $sign_input = implode('.', $token);

    $private_key = $this->get_private_key();
    $signature = $this->sign($sign_input, $private_key, $algorithm);

    $token[] = $this->base64_encode_url($signature);
    $token = implode('.', $token);

    // store token in database : set_jti, set_access_token, set_jwt_token, set_user_has_token...
    $store_jti = new JWTModel();

    if (!$store_jti->store_id($payload['jti']))
    {
      throw new \Exception('Failed to store token id.');
    }

    return $token;
  }

  public function verify()
  {
    $token = $this->get_token_from_header();

    if (strpos($token[0], 'Bearer'))
    {
      throw new \Exception('Invalid token type.');
    }

    if (!isset($token[1]))
    {
      throw new \Exception('Token not found.');
    }

    if (!stristr($token[1], '.'))
    {
      throw new \Exception("Token doesn't contain expected delimiter.");
    }

    if (count(explode('.', $token[1])) !== 3)
    {
      throw new \Exception("Token doesn't contain expected structure.");
    }

    // deconstruct and decode token structure
    list($header, $payload, $signature) = explode('.', $token[1]);
    $decoded_header = $this->decode_token_structure($header);
    $decoded_payload = $this->decode_token_structure($payload);

    if ($decoded_payload['iss'] !== 'http://ser.local')
    {
      throw new \Exception("Token doesn't contain expected issuer.");
    }

    if ($decoded_payload['iat'] > time())
    {
      throw new \Exception('Token was issued in the future (well played Jonas Kahnwald).');
    }

    if ($decoded_payload['exp'] < time())
    {
      throw new \Exception('Token expired.');
    }

    // $url = $decoded_payload['iss'] . '/.well-known/oauth-authorization-server';
    // $keys = (new TokenModel)->get_keys($url);

    $stored_token = new JWTModel();

    if (!$stored_token->find_by_id($decoded_payload['jti']))
    {
      throw new \Exception("Invalid token id.");
    }

    $private_key = $this->get_private_key();
    $public_key = $this->generate_public_key($private_key);
    $signature = $this->verify_signature("$header.$payload", $this->base64_decode_url($signature), $public_key['key'], $decoded_header['alg']);

    if (!$signature)
    {
      throw new \Exception("Token's signature couldn't be verified.");
    }

    return true;
  }

  public function generate_access_token($token, $scope = null)
  {
    /* response format from https://tools.ietf.org/html/rfc6749#section-4.4.3

    if (strpos($token[0], 'Bearer'))
    {
      throw new \Exception('Invalid token type.');
    }

    $token_type = explode(' ', $token[0]);

    HTTP/1.1 200 OK
    Content-Type: application/json;charset=UTF-8
    Cache-Control: no-store
    Pragma: no-cache

    {
      "access_token": "$token_type[0]",
      "token_type": "$token[1]",
      "expires_in": 3600,
      "scope": "$scope"
    }
    */
  }

  protected function get_token_from_header()
  {
    if (array_key_exists('HTTP_AUTHORIZATION', $_SERVER))
    {
      $authorization_header = $_SERVER['HTTP_AUTHORIZATION'];
    }
    elseif (array_key_exists('Authorization', $_SERVER))
    {
      $authorization_header = $_SERVER['Authorization'];
    }
    else
    {
      throw new \Exception('Unauthorized.');
    }

    if (preg_match('/Bearer\s(\S+)/', $authorization_header, $matches) === false)
    {
      throw new \Exception('Token type not found.');
    }

    return $matches;
  }

  protected function generate_header($algorithm)
  {
    return [
      'typ' => 'JWT',
      'alg' => $algorithm
    ];
  }

  protected function generate_payload($user_id, $scope = null)
  {
    $jti = $this->generate_jti(); //  $this->generate_access_token()
    $iat = time();
    $exp = $iat + 60 * 120 * 1 * 1; // expiration time set for an hour from now
    $payload = [
      'jti' => $jti,
      'id_user' => $user_id,
      'iss' => 'http://ser.local', // get from database ?
      'sub' => 'http://example.local/allowed', // get from database ?
      'iat' => $iat,
      'exp' => $exp,
      'token_type' => 'Bearer', // get from database ?
      'scope' => $scope
    ];

    return $payload;
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

  protected function get_private_key()
  {
    $private_key = file_get_contents('../keys/private.key');

    if ($private_key)
    {
      return $private_key;
    }
  }

  protected function generate_private_key()
  {
    // create openssl resource and save private key in a file
    $res = openssl_pkey_new([
      'private_key_bits' => 2048,
      'private_key_type' => OPENSSL_KEYTYPE_RSA
    ]);
    openssl_pkey_export($res, $private_key);

    if ($private_key)
    {
      file_put_contents('../keys/private.key', $private_key); // change permissions
      return $private_key;
    }
  }

  protected function generate_public_key($private_key)
  {
    // create public key from resource
    $res = openssl_pkey_get_private($private_key);

    if (!$res)
    {
      throw new \Exception("Invalid private key.");
    }

    $public_key = openssl_pkey_get_details($res);

    if (!$public_key)
    {
      throw new \Exception("Public key couldn't be generated.");
    }

    return $public_key;
  }

  protected function sign($input, $key, $algorithm)
  {
    if ($algorithm === 'HS256')
    {
      $algorithm = OPENSSL_ALGO_SHA256;
    }
    else
    {
      throw new \Exception('Invalid or unsupported sign algorithm.');
    }

    if (!openssl_sign($input, $signature, $key, $algorithm))
    {
      throw new \Exception("Couldn't signed token.");
    }

    return $signature;
  }

  protected function verify_signature($signature, $input, $key, $algorithm)
  {
    if ($algorithm === 'HS256')
    {
      $algorithm = OPENSSL_ALGO_SHA256;
    }
    else
    {
      throw new \Exception('Invalid or unsupported sign algorithm.');
    }

    return openssl_verify($signature, $input, $key, $algorithm);
  }

  public function encrypt($input)
  {
    // this for 2048 bit key for example, leaving some room
    $input = str_split($input, 200);
    $private_key = $this->get_private_key();
    $encrypted_token = '';

    foreach ($input as $chunk)
    {
      $encrypted_chunk = '';

      // using for example OPENSSL_PKCS1_PADDING as padding
      if (!openssl_private_encrypt($chunk, $encrypted_chunk, $private_key, OPENSSL_PKCS1_PADDING))
      {
        throw new \Exception(openssl_error_string());
      }

      $encrypted_token .= $encrypted_chunk;
    }

    return base64_encode($encrypted_token); // encoding the whole binary String as MIME base 64
  }

  // method not working
  public function decrypt($token)
  {
    // decode must be done before spliting for getting the binary String
    $token = str_split(base64_decode($token), 256);
    $private_key = $this->get_private_key();
    var_dump($private_key);
    $public_key = $this->generate_public_key($private_key);
    var_dump($public_key);
    $decrypted_token = '';

    foreach ($token as $chunk)
    {
      $decrypted_chunk = '';

      if (openssl_public_decrypt($chunk, $decrypted_chunk, $public_key, OPENSSL_PKCS1_PADDING))
      {
        throw new \Exception(openssl_error_string());
      }

      $decrypted_token .= $decrypted_chunk;
    }

    return $decrypted_token;
  }

  protected function encode_token_structure($array)
  {
    // if (!json_encode($input)) throw new \Exception('Error encoding input.');
    return $this->base64_encode_url(json_encode($array));
  }

  protected function decode_token_structure($array)
  {
    // if (!json_decode($input)) throw new \Exception('Error decoding input.');
    return json_decode(base64_decode($array), true);
  }

  protected function base64_encode_url($string)
  {
    return rtrim(strtr(base64_encode($string), '+/', '-_'), '=');
  }

  protected function base64_decode_url($string)
  {
    // if (!base64_decode($input)) throw new \Exception('Error decoding input.');
    return base64_decode(str_pad(strtr($string, '-_', '+/'), strlen($string) % 4, '=', STR_PAD_RIGHT));
  }
}
