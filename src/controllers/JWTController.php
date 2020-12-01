<?php
require_once('../tools/constants.php');

class JWTController
{
  // jwt generation, encryption, decryption and validation for performing oauth 2.0 client credentials grant type-ish (see https://tools.ietf.org/html/rfc6749#section-4.4)

  public function generate($user_id, $algorithm = 'HS256')
  {
    // jwt creation from https://tools.ietf.org/html/rfc7519#section-7.1 , plus private key encryption

    $user_id = filter_var($user_id, FILTER_SANITIZE_STRING);

    $headers = $this->create_header($algorithm);
    $payload = $this->create_payload($user_id, 'some_scope');
    $token = [
      $this->encode_token_structure($headers),
      $this->encode_token_structure($payload)
    ];
    $sign_input = implode('.', $token);

    $private_key = $this->get_private_key();
    $signature = $this->create_signature($sign_input, $private_key, $algorithm);
    $token[] = $this->base64_encode_url($signature);

    $token = implode('.', $token);
    $encrypted_token = $this->encrypt_token($token);

    if (empty($encrypted_token))
    {
      throw new \Exception("Token couldn't be encrypted.");
    }

    // store jwt in database
    $new_token = new JWTModel();

    if (!$new_token->create($payload['jti'], $encrypted_token, $user_id))
    {
      throw new \Exception("Token couldn't be stored in database.");
    }

    return $encrypted_token;
  }

  public function verify($encrypted_token = null)
  {
    // jwt validation from https://tools.ietf.org/html/rfc7519#section-7.2 , plus public key decryption

    if (is_null($encrypted_token))
    {
      $encrypted_token = $this->get_token_from_header()['jwt'];
      $has_token = false;
    }

    $token = $this->decrypt_token($encrypted_token);

    if (empty($token))
    {
      throw new \Exception("Token couldn't be decrypted.");
    }

    if (!stristr($token, '.'))
    {
      throw new \Exception("Token doesn't contain expected delimiter.");
    }

    if (count(explode('.', $token)) !== 3)
    {
      throw new \Exception("Token doesn't contain expected structure.");
    }

    // split and decode token structure
    list($header, $payload, $signature) = explode('.', $token);
    $decoded_header = $this->decode_token_structure($header);
    $decoded_payload = $this->decode_token_structure($payload);

    if ($decoded_payload['iss'] !== ISSUER)
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
      /*
      if refresh_token request
        refresh token
      else
        revoke token
      */
    }

    $stored_token = new JWTModel();
    $jti = filter_var($decoded_payload['jti'], FILTER_SANITIZE_STRING);

    if (!$stored_token->find_by_jti($jti))
    {
      throw new \Exception('Invalid token ID.');
    }

    $jwt = filter_var($encrypted_token, FILTER_SANITIZE_STRING);

    if (!$stored_token->find_by_jwt($jwt))
    {
      throw new \Exception('Invalid token.');
    }

    if (isset($has_token) && !$has_token)
    {
      $user_id = filter_var($decoded_payload['id_user'], FILTER_SANITIZE_STRING);

      if (!$stored_token->find_by_user($user_id))
      {
        throw new \Exception('Invalid user ID.');
      }
    }

    $private_key = $this->get_private_key();
    $public_key = $this->get_public_key($private_key);
    $signature = $this->verify_signature("$header.$payload", $this->base64_decode_url($signature), $public_key['key'], $decoded_header['alg']);

    if (!$signature)
    {
      throw new \Exception("Token's signature couldn't be verified.");
    }

    return true;
  }

  // this method must be changed
  public function generate_access_token()
  {
    // response format from https://tools.ietf.org/html/rfc6749#section-5.1

    $stored_token = $this->get_token_from_header(); // $this->generate_jti();

    if (!$stored_token)
    {
      throw new \Exception("Token wasn't found neither in header, nor in database.");
    }

    $user_id = 'agent_' . $this->generate_jti() . $stored_token['users_id'];
    $encrypted_user_id = $this->encrypt_token($user_id);

    if (empty($encrypted_user_id))
    {
      throw new \Exception("User ID couldn't be encrypted.");
    }

    $exp = time() + 10 * 60 * 1 * 1; // expiration time set to ten minutes from now
    $access_token = [
      'access_token' => $stored_token['jwt'],
      'token_type' => 'Bearer',
      'user_id' => $encrypted_user_id,
      'expires_in' => $exp
    ];
    $encoded_access_token = $this->encode_token_structure($access_token);
    $encrypted_access_token = $this->encrypt_token($encoded_access_token);

    if (empty($encrypted_access_token))
    {
      throw new \Exception("Access token couldn't be encrypted.");
    }

    // store access token in database

    return $encrypted_access_token;
  }

  // method not tested yet
  public function verify_access_token($encrypted_access_token = null)
  {
    // if (is_null($encrypted_access_token))
    // {
    //   $encrypted_access_token = $this->get_token_from_header();
    //   $has_token = false;
    // }

    $decrypted_access_token = $this->decrypt_token($encrypted_access_token);
    $access_token = $this->decode_token_structure($decrypted_access_token);

    if (empty($access_token))
    {
      throw new \Exception("Access token couldn't be decrypted.");
    }

    if (empty($access_token['access_token']))
    {
      throw new \Exception("Access token wasn't neither in header nor in database.");
    }

    if ($access_token['expires_in'] < time())
    {
      throw new \Exception('Access token expired.');
      // revoke token
    }

    // $stored_token = new JWTModel();
    // $token = filter_var($access_token['access_token'], FILTER_SANITIZE_STRING);
    //
    // if (!$stored_token->find_by_access_token($token))
    // {
    //   throw new \Exception('Invalid access token.');
    // }

    $decrypted_user_id = $this->decrypt_token($access_token['user_id']);
    $user_id = substr($decrypted_user_id, -1);
    $access_token['user_id'] = $user_id;

    // if (isset($has_token) && !$has_token)
    // {
    //   $user_id = filter_var($user_id, FILTER_SANITIZE_STRING);
    //
    //   // find_by_user method not modified yet
    //   if (!$stored_token->find_by_user('access_token', $user_id))
    //   {
    //     throw new \Exception('Invalid user ID.');
    //   }
    // }

    return json_decode($access_token, true);
  }

  public function refresh_token()
  {
    $stored_token = $this->get_token_from_header();

    if (!$stored_token)
    {
      throw new \Exception("Token wasn't found neither in header, nor in database.");
    }

    $new_token = $this->generate($stored_token['users_id']);

    if (empty($new_token))
    {
      throw new \Exception("Token couldn't be generated.");
    }

    if (!(new JWTModel)->delete($stored_token['jti']))
    {
      throw new \Exception("Token couldn't be revoked and deleted from database.");
    }

    return $new_token;
  }

  public function revoke_token()
  {
    $stored_token = $this->get_token_from_header();

    if (!$stored_token)
    {
      throw new \Exception("Token wasn't found neither in header, nor in database.");
    }

    if (!(new JWTModel)->delete($stored_token['jti']))
    {
      throw new \Exception("Token couldn't be revoked and deleted from database.");
    }

    return true;
  }

  private function create_header($algorithm)
  {
    // jose header format from https://tools.ietf.org/html/rfc7519#section-5

    if (strtolower($algorithm) !== 'hs256')
    {
      throw new \Exception('Invalid or unsupported algorithm.');
    }

    return [
      'typ' => 'JWT',
      'alg' => $algorithm
    ];
  }

  private function create_payload($user_id, $scope = null)
  {
    // jwt claims format from https://tools.ietf.org/html/rfc7519#section-4

    $jti = $this->generate_jti();
    $iat = time();
    $exp = $iat + 60 * 60 * 1 * 1; // expiration time set to an hour from now
    $payload = [
      'jti' => $jti,
      'id_user' => $user_id,
      'iss' => ISSUER,
      'sub' => 'http://service.local/allowed-service', // get from database ?
      'iat' => $iat, // add 'nbf' claim
      'exp' => $exp,
      'token_type' => 'Bearer',
      'scope' => $scope
    ];

    return $payload;
  }

  private function create_signature($input, $key, $algorithm)
  {
    if (strtolower($algorithm) === 'hs256')
    {
      $algorithm = OPENSSL_ALGO_SHA256;
    }
    else
    {
      throw new \Exception('Invalid or unsupported sign algorithm.');
    }

    if (!openssl_sign($input, $signature, $key, $algorithm))
    {
      throw new \Exception("Token couldn't be signed.");
    }

    return $signature;
  }

  private function verify_signature($signature, $input, $key, $algorithm)
  {
    if (strtolower($algorithm) === 'hs256')
    {
      $algorithm = OPENSSL_ALGO_SHA256;
    }
    else
    {
      throw new \Exception('Invalid or unsupported sign algorithm.');
    }

    return openssl_verify($signature, $input, $key, $algorithm);
  }

  public function get_token_from_header()
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

    $token_type = explode(' ', $authorization_header)[0];

    if (!preg_match("/$token_type\s(\S+)/", $authorization_header, $matches))
    {
      throw new \Exception("Token type wasn't found in header.");
    }

    if (strpos($matches[0], $token_type))
    {
      throw new \Exception('Invalid token type.');
    }

    if (!isset($matches[1]))
    {
      throw new \Exception("Token wasn't found in header.");
    }

    if ($token_type === 'Basic')
    {
      return $matches[1];
    }
    elseif ($token_type === 'Bearer')
    {
      // if request === 'jwt_request'
      $jwt = filter_var($matches[1], FILTER_SANITIZE_STRING);
      $stored_token = (new JWTModel)->find_by_jwt($jwt);

      return $stored_token;

      // elseif request === 'access_token'
    }
  }

  private function generate_jti($lenght = 40)
  {
    if (function_exists('random_bytes'))
    {
      $bytes = random_bytes(ceil($lenght / 2));
    }
    elseif (function_exists('openssl_random_pseudo_bytes'))
    {
      $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
    }
    elseif (function_exists('mcrypt_create_iv'))
    {
      $bytes = mcrypt_create_iv(ceil($lenght / 2), MCRYPT_DEV_URANDOM);
    }
    else
    {
      $bytes = mt_rand() . mt_rand() . mt_rand() . mt_rand() . microtime(true) . uniqid(mt_rand(), true);
    }

    return substr(bin2hex($bytes), 0, $lenght);
  }

  private function get_private_key()
  {
    $private_key = file_get_contents('../keys/private.key');

    if ($private_key)
    {
      return $private_key;
    }
  }

  private function generate_private_key()
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

  private function get_public_key($private_key)
  {
    // create public key from resource
    $res = openssl_pkey_get_private($private_key);

    if (!$res)
    {
      throw new \Exception('Invalid private key.');
    }

    $public_key = openssl_pkey_get_details($res);

    if (!$public_key)
    {
      throw new \Exception("Public key couldn't be generated.");
    }

    return $public_key;
  }

  private function encrypt_token($input)
  {
    // for a 2048 bit key
    $input = str_split($input, 200);
    $private_key = $this->get_private_key();
    $encrypted_token = '';

    foreach ($input as $chunk)
    {
      $encrypted_chunk = '';

      // using OPENSSL_PKCS1_PADDING as padding
      if (!openssl_private_encrypt($chunk, $encrypted_chunk, $private_key, OPENSSL_PKCS1_PADDING))
      {
        throw new \Exception(openssl_error_string());
      }

      $encrypted_token .= $encrypted_chunk;
    }

    return base64_encode($encrypted_token);
  }

  public function decrypt_token($token)
  {
    // decode must be done before spliting
    $token = str_split(base64_decode($token), 256);
    $private_key = $this->get_private_key();
    $public_key = $this->get_public_key($private_key);
    $decrypted_token = '';

    foreach ($token as $chunk)
    {
      $decrypted_chunk = '';

      if (!openssl_public_decrypt($chunk, $decrypted_chunk, $public_key['key'], OPENSSL_PKCS1_PADDING))
      {
        throw new \Exception(openssl_error_string());
      }

      $decrypted_token .= $decrypted_chunk;
    }

    return $decrypted_token;
  }

  private function encode_token_structure($array)
  {
    // if (!json_encode($input)) throw new \Exception('Error encoding input.');
    return $this->base64_encode_url(json_encode($array));
  }

  private function decode_token_structure($array)
  {
    // if (!json_decode($input)) throw new \Exception('Error decoding input.');
    return json_decode(base64_decode($array), true);
  }

  private function base64_encode_url($string)
  {
    return rtrim(strtr(base64_encode($string), '+/', '-_'), '=');
  }

  private function base64_decode_url($string)
  {
    // if (!base64_decode($input)) throw new \Exception('Error decoding input.');
    return base64_decode(str_pad(strtr($string, '-_', '+/'), strlen($string) % 4, '=', STR_PAD_RIGHT));
  }
}
