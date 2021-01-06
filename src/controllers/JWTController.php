<?php
require './tools/constants.php';

class JWTController
{
  // jwt generation, encryption, decryption and validation for performing oauth 2.0 client credentials grant type-ish (see https://tools.ietf.org/html/rfc6749#section-4.4)

  public function generate($user_id, $algorithm = 'HS256') // 'RS256'
  {
    // jwt creation from https://tools.ietf.org/html/rfc7519#section-7.1 , plus private key encryption

    $user_id = filter_var($user_id, FILTER_SANITIZE_STRING);

    if (strtolower($algorithm) !== 'hs256')
    {
      // return $this->response_handler('error', 'Invalid or unsupported algorithm.');
      return 'Invalid or unsupported algorithm.';
    }

    $headers = $this->create_header($algorithm);
    $payload = $this->create_payload($user_id, 'some_scope');
    $token = [
      $this->encode_token_structure($headers),
      $this->encode_token_structure($payload)
    ];
    $sign_input = implode('.', $token);

    $private_key = $this->get_private_key();

    if (!$private_key)
    {
      // return $this->response_handler('error', 'Invalid private key.');
      return 'Invalid private key.';
    }

    $signature = $this->create_signature($sign_input, $private_key, $algorithm);

    if (!$signature)
    {
      // return $this->response_handler('error', "Token couldn't be signed.");
      return "Token couldn't be signed.";
    }

    $token[] = $this->base64_encode_url($signature);
    $token = implode('.', $token);
    // $encrypted_token = $this->encrypt_token($token);
    $encrypted_token = $this->encrypt_token($token, $private_key);

    if (!$encrypted_token)
    {
      // return $this->response_handler('error', openssl_error_string());
      return openssl_error_string();
    }

    // if (empty($encrypted_token))
    // {
    //   throw new \Exception("Token couldn't be encrypted.");
    // }
    //
    // store jwt in database
    // $new_token = new JWTModel();
    //
    // if (!$new_token->create('authentication', $payload['jti'], $encrypted_token, $user_id))
    // {
    //   throw new \Exception("Token couldn't be stored in database.");
    // }

    if (!(new JWTModel)->create('authentication', $payload['jti'], $encrypted_token, $user_id))
    {
      // return $this->response_handler('error', "Token couldn't be stored in database.");
      return "Token couldn't be stored in database.";
    }

    $response = [
      'response_type' => 'authentication_token',
      'response_value' => $encrypted_token
    ];

    return $response;
  }

  public function verify()
  {
    // jwt validation from https://tools.ietf.org/html/rfc7519#section-7.2 , plus public key decryption

    $valid_uris = ['request_token', 'access_token', 'refresh_token', 'revoke_token'];
    $num_args = func_num_args();
    $arg = (sizeof(func_get_args()) > 0) ? func_get_args()[0] : ''; // used in pre-production
    // $arg = func_get_arg(0);

    // if ($num_args === 1)
    // {
    //   if (in_array($arg, $valid_uris))
    //   {
    //     $uri = $arg;
    //     $encrypted_token = $this->get_token_from_header()['token'];
    //     $has_token = false;
    //   }
    //   else
    //   {
    //     $encrypted_token = $arg;
    //   }
    // }
    // else
    // {
      // $encrypted_token = $this->get_token_from_header()['token'];
    //   $has_token = false;
    // }

    if ($num_args === 0 || $num_args === 1 && in_array($arg, $valid_uris))
    {
      $uri = $arg;
      $has_token = false;
      $encrypted_token = $this->get_token_from_header();

      if (!is_array($encrypted_token))
      {
        // return $this->response_handler('error', $encrypted_token);
        $encrypted_token = $error_message;
        return $error_message;
      }

      $encrypted_token = $encrypted_token['response_value']['token'];
    }
    elseif ($num_args === 1 && !in_array($arg, $valid_uris))
    {
      $encrypted_token = $arg;
    }

    $private_key = $this->get_private_key();

    if (!$private_key)
    {
      // return $this->response_handler('error', 'Invalid private key.');
      return 'Invalid private key.';
    }

    $public_key = $this->get_public_key($private_key);

    if (!$public_key)
    {
      // return $this->response_handler('error', "Public key couldn't be generated.");
      return "Public key couldn't be generated.";
    }

    $decrypted_token = $this->decrypt_token($encrypted_token, $public_key['key']); // rename variable to $decrypted_token

    if (!$decrypted_token)
    {
      // return $this->response_handler('error', openssl_error_string());
      return openssl_error_string();
    }

    // if (empty($token))
    // {
    //   throw new \Exception("Token couldn't be decrypted.");
    // }

    if (!stristr($decrypted_token, '.'))
    {
      // throw new \Exception("Token doesn't contain expected delimiter.");
      // return $this->response_handler('error', "Token doesn't contain expected delimiter.");
      return "Token doesn't contain expected delimiter.";
    }

    if (count(explode('.', $decrypted_token)) !== 3)
    {
      // throw new \Exception("Token doesn't contain expected structure.");
      // return $this->response_handler('error', "Token doesn't contain expected structure.");
      return "Token doesn't contain expected structure.";
    }

    // split and decode token structure
    list($header, $payload, $signature) = explode('.', $decrypted_token);
    $decoded_header = $this->decode_token_structure($header);
    $decoded_payload = $this->decode_token_structure($payload);

    if ($decoded_payload['iss'] !== ISSUER)
    {
      // throw new \Exception("Token doesn't contain expected issuer.");
      // return $this->response_handler('error', "Token doesn't contain expected issuer.");
      return "Token doesn't contain expected issuer.";
    }

    $token_type = 'authentication';
    $token_model = new JWTModel();
    $jti = filter_var($decoded_payload['jti'], FILTER_SANITIZE_STRING);
    $stored_token = $token_model->find_by_jti($token_type, $jti);

    if (!$stored_token)
    {
      // throw new \Exception('Invalid token ID.');
      // return $this->response_handler('error', 'Invalid token ID.');
      return 'Invalid token ID.';
    }

    $jwt = filter_var($encrypted_token, FILTER_SANITIZE_STRING);

    if ($token_model->find_by_token('blacklist', $jwt))
    {
      // throw new \Exception('Token was found in the blacklist.');
      // return $this->response_handler('error', 'Token was found in the blacklist.');
      return 'Token was found in the blacklist.';
    }

    if (!$token_model->find_by_token($token_type, $jwt))
    {
      // throw new \Exception('Invalid token.');
      // return $this->response_handler('error', 'Invalid token.');
      return 'Invalid token.';
    }

    if (isset($has_token) && !$has_token)
    {
      $user_id = filter_var($decoded_payload['id_user'], FILTER_SANITIZE_STRING);

      if (!$token_model->find_by_user($token_type, $user_id))
      {
        // throw new \Exception('Invalid user ID.');
        // return $this->response_handler('error', 'Invalid user ID.');
        return 'Invalid user ID.';
      }
    }

    $now = date('Y-m-d H:i:s');

    // this condition is not working: $stored_token['created_at'] > $now
    if ($decoded_payload['iat'] > time())
    {
      // throw new \Exception('Token was issued in the future.');
      // return $this->response_handler('error', 'Token was issued in the future.');
      return 'Token was issued in the future.';
    }

    if ($decoded_payload['exp'] < time() || $stored_token['expires_at'] < $now)
    {
      if (isset($uri) && $uri === 'refresh_token')
      {
        if (!$this->refresh_token())
        {
          // throw new \Exception("Token couldn't be refreshed.");
          // return $this->response_handler('error', "Token couldn't be refreshed.");
          return "Token couldn't be refreshed.";
        }
      }

      $revoked_tokens = $this->revoke_tokens($token_model, $stored_token['jti'], $stored_token['token'], $stored_token['users_id']);

      if ($revoked_tokens !== true)
      {
        // throw new \Exception("Tokens couldn't be revoked and deleted from database.");
        // return $this->response_handler('error', $revoked_tokens);
        $error_message = $revoked_tokens;
        return $error_message;
      }

      // if (!$token_model->add_to_blacklist($stored_token['jti'], $stored_token['token'], $token_type, $stored_token['users_id']))
      // {
      //   throw new \Exception("Token couldn't be added to blacklist.");
      // }
      //
      // if (!$token_model->delete($token_type, $stored_token['jti']))
      // {
      //   throw new \Exception("Token couldn't be revoked and deleted from database.");
      // }

      // after deleting the token, no request works
      // throw new \Exception('Authentication token expired.');
      // return $this->response_handler('error', 'Authentication token expired. Please, login again.');
      return 'Authentication token expired. Please, login again.';
    }

    // $private_key = $this->get_private_key();
    // $public_key = $this->get_public_key($private_key);
    $signature = $this->verify_signature("$header.$payload", $this->base64_decode_url($signature), $public_key['key'], $decoded_header['alg']);

    if (!$signature)
    {
      // throw new \Exception("Token's signature couldn't be verified.");
      // return $this->response_handler('error', "Token's signature couldn't be verified.");
      return "Token's signature couldn't be verified.";
    }

    $response = [
      'response_type' => 'verified_token',
      'response_value' => true
    ];

    return $response;
  }

  public function generate_access_token($jti, $user_id)
  {
    // response format from https://tools.ietf.org/html/rfc6749#section-5.1

    $license_number = 'agent_' . $this->generate_jti() . $user_id;
    $private_key = $this->get_private_key();

    if (!$private_key)
    {
      // return $this->response_handler('error', 'Invalid private key.');
      return 'Invalid private key.';
    }

    $encrypted_license = $this->encrypt_token($license_number, $private_key);

    if (!$encrypted_license)
    {
      return openssl_error_string();
    }

    $access_token = $this->create_access_token($encrypted_license);
    $encoded_access_token = $this->encode_token_structure($access_token);
    $encrypted_access_token = $this->encrypt_token($encoded_access_token, $private_key);

    if (!$encrypted_access_token)
    {
      return openssl_error_string();
    }

    // store access token in database
    // $new_token = new JWTModel();
    //
    // if (!$new_token->create('authorization', $jti, $encrypted_access_token, $user_id))
    // {
    //   throw new \Exception("Token couldn't be stored in database.");
    // }

    if (!(new JWTModel)->create('authorization', $jti, $encrypted_access_token, $user_id))
    {
      // throw new \Exception("Token couldn't be stored in database.");
      return "Token couldn't be stored in database.";
    }

    // return $encrypted_access_token;

    $response = [
      'response_type' => 'authorization_token',
      'response_value' => $encrypted_access_token
    ];

    return $response;
  }

  public function verify_access_token()
  {
    $num_args = func_num_args();
    $arg = func_get_arg(0);

    if ($num_args === 1)
    {
      $encrypted_access_token = $arg;
    }
    else
    {
      // code...
    }

    $private_key = $this->get_private_key();

    if (!$private_key)
    {
      // return $this->response_handler('error', 'Invalid private key.');
      return 'Invalid private key.';
    }

    $public_key = $this->get_public_key($private_key);

    if (!$public_key)
    {
      // return $this->response_handler('error', "Public key couldn't be generated.");
      return "Public key couldn't be generated.";
    }

    $decrypted_access_token = $this->decrypt_token($encrypted_access_token, $public_key['key']);

    if (!$decrypted_access_token)
    {
      // return $this->response_handler('error', openssl_error_string());
      return openssl_error_string();
    }

    $access_token = $this->decode_token_structure($decrypted_access_token);

    // if (empty($access_token))
    // {
    //   throw new \Exception("Access token couldn't be decrypted.");
    // }

    if (!is_array($access_token))
    {
      // throw new \Exception("Authorization token doesn't contain expected structure.");
      return "Access token doesn't contain expected structure.";
    }

    if (empty($access_token['access_token']) && empty($access_token['user_id']))
    {
      // throw new \Exception("Access token wasn't found neither in header nor in database.");
      return "Access token wasn't found neither in header nor in database.";
    }

    if (!is_string($access_token['access_token']) && !is_string($access_token['user_id']))
    {
      // throw new \Exception("Authorization token isn't of the type string.");
      return "Access token isn't of the type string.";
    }

    $token_type = 'authorization';
    $token_model = new JWTModel();
    $token = filter_var($encrypted_access_token, FILTER_SANITIZE_STRING);
    $stored_token = $token_model->find_by_token($token_type, $token);

    if (!$stored_token)
    {
      // throw new \Exception('Invalid access token.');
      return 'Invalid access token.';
    }

    if ($token_model->find_by_token('blacklist', $stored_token['token']))
    {
      // throw new \Exception('Access token was found in the blacklist.');
      return 'Access token was found in the blacklist.';
    }

    $now = date('Y-m-d H:i:s');

    if ($access_token['expires_in'] < time() || $stored_token['expires_at'] < $now)
    {
      if (!$token_model->add_to_blacklist($stored_token['jti'], $stored_token['token'], $token_type, $stored_token['users_id']))
      {
        // throw new \Exception("Access token couldn't be added to blacklist.");
        return "Access token couldn't be added to blacklist.";
      }

      if (!$token_model->delete($token_type, $stored_token['jti']))
      {
        // throw new \Exception("Access token couldn't be revoked and deleted from database.");
        return "Access token couldn't be revoked and deleted from database.";
      }

      // throw new \Exception('Access token expired.');
      return 'Access token expired.';
    }

    $decrypted_user_id = $this->decrypt_token($access_token['user_id'], $public_key['key']);
    $get_user_id = substr($decrypted_user_id, -1);
    $user_id = filter_var($get_user_id, FILTER_SANITIZE_STRING);


    if (isset($has_token) && !$has_token)
    {
      if (!$token_model->find_by_user($token_type, $user_id))
      {
        // throw new \Exception('Invalid user ID.');
        return 'Invalid user ID.';
      }
    }

    // $access_token['user_id'] = $user_id;
    // return $access_token;

    $response = [
      'response_type' => 'verified_token',
      'response_value' => $user_id
    ];

    return $response;
  }

  // this method must be changed
  public function refresh_token()
  {
    $stored_token = $this->get_token_from_header();

    if (!is_array($stored_token))
    {
      return $this->response_handler('error', $stored_token);
    }

    $stored_token = $stored_token['response_value'];

    // if (!$stored_token)
    // {
    //   throw new \Exception("Token wasn't found neither in header, nor in database.");
    // }

    $new_token = $this->generate($stored_token['users_id']);

    if (empty($new_token))
    {
      throw new \Exception("Token couldn't be generated.");
    }

    if (!(new JWTModel)->delete('authentication', $stored_token['jti']))
    {
      throw new \Exception("Token couldn't be revoked and deleted from database.");
    }

    return $new_token;
  }

  // revoke both authentication and authorization tokens
  public function revoke_token()
  {
    $stored_token = $this->get_token_from_header();

    if (!is_array($stored_token))
    {
      return $this->response_handler('error', $stored_token);
    }

    $stored_token = $stored_token['response_value'];
    $token_model = new JWTModel();

    // if (!$stored_token)
    // {
    //   throw new \Exception("Authentication token wasn't found neither in header, nor in database.");
    // }

    if (!$token_model->delete('authentication', $stored_token['jti']))
    {
      // throw new \Exception("Authentication token couldn't be revoked and deleted from database.");
      return "Authentication token couldn't be revoked and deleted from database.";
    }

    if (!$token_model->find_by_jti('authorization', $stored_token['jti']))
    {
      // throw new \Exception("Authorization token has already been revoked or deleted from database.");
      return "Authorization token has already been revoked or deleted from database.";
    }

    if (!$token_model->delete('authorization', $stored_token['jti']))
    {
      // throw new \Exception("Authorization token couldn't be revoked and deleted from database.");
      return "Authorization token couldn't be revoked and deleted from database.";
    }

    return true;
  }

  // revoke and blacklist both authentication and authorization tokens
  private function revoke_tokens($token_model, $jti, $token, $user_id)
  {
    if (!$token_model->add_to_blacklist($jti, $token, 'authentication', $user_id))
    {
      // throw new \Exception("Authentication token couldn't be added to blacklist.");
      return "Authentication token couldn't be added to blacklist.";
    }

    if (!$token_model->add_to_blacklist($jti, $token, 'authorization', $user_id))
    {
      // throw new \Exception("Authorization token couldn't be added to blacklist.");
      return "Authorization token couldn't be added to blacklist.";
    }

    if (!$token_model->delete('authentication', $jti))
    {
      // throw new \Exception("Authentication token couldn't be revoked and deleted from database.");
      return "Authentication token couldn't be revoked and deleted from database.";
    }

    if (!$token_model->delete('authorization', $jti))
    {
      // throw new \Exception("Authorization token couldn't be revoked and deleted from database.");
      return "Authorization token couldn't be revoked and deleted from database.";
    }

    return true;
  }

  private function create_header($algorithm)
  {
    // jose header format from https://tools.ietf.org/html/rfc7519#section-5

    // if (strtolower($algorithm) !== 'hs256')
    // {
    //   throw new \Exception('Invalid or unsupported algorithm.');
    // }

    $header = [
      'typ' => 'JWT',
      'alg' => $algorithm
    ];

    return $header;
  }

  private function create_payload($user_id, $scope = null)
  {
    // jwt claims format from https://tools.ietf.org/html/rfc7519#section-4

    $jti = $this->generate_jti();
    $iat = time();
    $exp = $iat + 3 * 60 * 1 * 1; // expiration time set to an hour from now
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
    if (strtolower($algorithm) === 'hs256') // 'rs256'
    {
      $algorithm = OPENSSL_ALGO_SHA256;
    }
    else
    {
      throw new \Exception('Invalid or unsupported sign algorithm.');
    }

    // if (!openssl_sign($input, $signature, $key, $algorithm))
    // {
    //   throw new \Exception("Token couldn't be signed.");
    // }

    openssl_sign($input, $signature, $key, $algorithm);

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

    // openssl_verify($signature, $input, $key, $algorithm)

    return openssl_verify($signature, $input, $key, $algorithm);
  }

  private function get_sign_algorithm($algorithm)
  {
    $algorithm = strtolower($algorithm);

    if ($algorithm === 'hs256')
    {
      $algorithm = 'sha256'; // hash_hmac('sha256', $input, $key, true)
    }
    elseif ($algorithm === 'hs384')
    {
      $algorithm = 'sha384'; // hash_hmac('sha384', $input, $key, true)
    }
    elseif ($algorithm === 'hs512')
    {
      $algorithm = 'sha512'; // hash_hmac('sha512', $input, $key, true)
    }
    elseif ($algorithm === 'rs256')
    {
      $algorithm = OPENSSL_ALGO_SHA256;
    }
    elseif ($algorithm === 'RS384')
    {
      $algorithm = OPENSSL_ALGO_SHA384;
    }
    elseif ($algorithm === 'RS512')
    {
      $algorithm = OPENSSL_ALGO_SHA512;
    }
    else
    {
      return false; // 'Invalid or unsupported sign algorithm.'
    }

    return $algorithm;
  }

  private function create_access_token($encrypted_license)
  {
    // response format from https://tools.ietf.org/html/rfc6749#section-5.1

    // $user_id = 'agent_' . $this->generate_jti() . $user_id;
    // $encrypted_user_id = $this->encrypt_token($user_id);
    //
    // if (empty($encrypted_user_id))
    // {
    //   throw new \Exception("User ID couldn't be encrypted.");
    // }

    $exp = time() + 2 * 60 * 1 * 1; // expiration time set to ten minutes from now
    $access_token = [
      'access_token' => $this->generate_jti(), // sign ?
      'token_type' => 'Bearer',
      'expires_in' => $exp,
      'user_id' => $encrypted_license
    ];

    return $access_token;
  }

  // method not working yet
  public function get_origin_from_header()
  {
    if (array_key_exists('HTTP_HOST', $_SERVER))
    {
      $origin = $_SERVER['HTTP_HOST'];
    }
    elseif (array_key_exists('HTTP_ORIGIN', $_SERVER))
    {
      $origin = $_SERVER['HTTP_ORIGIN'];
    }
    elseif (array_key_exists('HTTP_REFERER', $_SERVER))
    {
      $origin = $_SERVER['HTTP_REFERER'];
    }
    elseif (array_key_exists('Origin', apache_request_headers()))
    {
      $origin = apache_request_headers()['Origin'];
    }
    else
    {
      return "Request's origin domain wasn't found.";
    }

    // if (!in_array($origin, AUTHORIZED_DOMAINS))
    // {
    //   throw new \Exception('Unauthorized domain.');
    // }

    return true;
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
    elseif (array_key_exists('Authorization', apache_request_headers()))
    {
      $authorization_header = apache_request_headers()['Authorization'];
    }
    else
    {
      // throw new \Exception('Unauthorized.');
      return 'Unauthorized';
    }

    $token_type = explode(' ', $authorization_header)[0];

    if (!preg_match("/$token_type\s(\S+)/", $authorization_header, $matches))
    {
      // throw new \Exception("Token type wasn't found in header.");
      return "Token type wasn't found in header.";
    }

    if (strpos($matches[0], $token_type))
    {
      // throw new \Exception('Invalid token type.');
      return 'Invalid token type.';
    }

    if (!isset($matches[1]) || empty($matches[1]))
    {
      // throw new \Exception("Token wasn't found in header.");
      return "Token wasn't found in header.";
    }

    if (strtolower($token_type) === 'basic')
    {
      $response = [
        'response_type' => 'client_credentials',
        'response_value' => $matches[1]
      ];

      return $response;
    }
    elseif (strtolower($token_type) === 'bearer')
    {
      $token = filter_var($matches[1], FILTER_SANITIZE_STRING);
      $stored_token = (new JWTModel)->find_by_token('authentication', $token);

      $response = [
        'response_type' => 'authentication_token',
        'response_value' => $stored_token
      ];

      return $response;
    }

    // $token_type = explode(' ', $authorization_header)[0];
    //
    // if (!preg_match("/$token_type\s(\S+)/", $authorization_header, $matches))
    // {
    //   throw new \Exception("Token type wasn't found in header.");
    // }
    //
    // if (strpos($matches[0], $token_type))
    // {
    //   throw new \Exception('Invalid token type.');
    // }
    //
    // if (!isset($matches[1]))
    // {
    //   throw new \Exception("Token wasn't found in header.");
    // }
    //
    // if ($token_type === 'Basic')
    // {
    //   return $matches[1];
    // }
    // elseif ($token_type === 'Bearer')
    // {
    //   // if request === 'jwt_request'
    //
    //   $token = filter_var($matches[1], FILTER_SANITIZE_STRING);
    //   $stored_token = (new JWTModel)->find_by_token('authentication', $token);
    //
    //   return $stored_token;
    //
    //   // elseif request === 'access_token'
    // }
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
    $private_key = file_get_contents('./keys/private.key');

    // if ($private_key)
    // {
    //   return $private_key;
    // }

    return $private_key;
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
      file_put_contents('./keys/private.key', $private_key); // change permissions
      return $private_key;
    }
  }

  private function get_public_key($private_key)
  {
    // create public key from resource
    $res = openssl_pkey_get_private($private_key);

    // if (!$res)
    // {
    //   throw new \Exception('Invalid private key.');
    // }

    $public_key = openssl_pkey_get_details($res);

    // if (!$public_key)
    // {
    //   throw new \Exception("Public key couldn't be generated.");
    // }

    return $public_key;
  }

  private function encrypt_token($input, $private_key)
  {
    // for a 2048 bit key
    $input = str_split($input, 200);
    // $private_key = $this->get_private_key();
    $encrypted_token = '';

    foreach ($input as $chunk)
    {
      $encrypted_chunk = '';

      // using OPENSSL_PKCS1_PADDING as padding
      if (!openssl_private_encrypt($chunk, $encrypted_chunk, $private_key, OPENSSL_PKCS1_PADDING))
      {
        // throw new \Exception(openssl_error_string());
        return false;
      }

      $encrypted_token .= $encrypted_chunk;
    }

    return base64_encode($encrypted_token);
  }

  public function decrypt_token($token, $public_key)
  {
    // decode must be done before spliting
    $token = str_split(base64_decode($token), 256);
    // $private_key = $this->get_private_key();
    // $public_key = $this->get_public_key($private_key);
    $decrypted_token = '';

    foreach ($token as $chunk)
    {
      $decrypted_chunk = '';

      if (!openssl_public_decrypt($chunk, $decrypted_chunk, $public_key, OPENSSL_PKCS1_PADDING))
      {
        // throw new \Exception(openssl_error_string());
        return false;
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

  private function response_handler($type, $value)
  {
    $response = [
      'response_type' => $type,
      'response_value' => $value
    ];

    return json_encode($response);
  }
}
