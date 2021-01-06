<?php
require './tools/constants.php';

class ReponseController
{
  public function get_origin_from_header()
  {
    if (array_key_exists('HTTP_ORIGIN', $_SERVER))
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
      return "Origin of the request wasn't found.";
    }

    if (!in_array($origin, VALID_DOMAINS))
    {
      return 'Unauthorized domain.';
    }

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
      return 'Authentication token not found.';
    }

    $token_type = explode(' ', $authorization_header)[0];

    if (!preg_match("/$token_type\s(\S+)/", $authorization_header, $matches))
    {
      return "Token type wasn't found in header.";
    }

    if (!isset($matches[1]) || empty($matches[1]))
    {
      return "Token wasn't found in header.";
    }

    $response = [
      'response_type' => 'authentication_token',
      'response_value' => $matches[1]
    ];

    return $response;
  }

  public function verify_authorization_token($encrypted_authorization_token)
  {
    $private_key = $this->get_private_key();

    if (!$private_key)
    {
      return 'Invalid private key.';
    }

    $public_key = $this->get_public_key($private_key);

    if (!$public_key)
    {
      return "Public key couldn't be generated.";
    }

    $decrypted_authorization_token = $this->decrypt_token($encrypted_authorization_token, $public_key['key']);

    if (!$decrypted_authorization_token)
    {
      return openssl_error_string();
    }

    $authorization_token = $this->decode_token_structure($decrypted_authorization_token);

    if (!is_array($authorization_token))
    {
      return "Authorization token doesn't contain expected structure.";
    }

    if (empty($authorization_token['access_token']) && empty($authorization_token['user_id']))
    {
      return "Authorization token wasn't found neither in header nor in database.";
    }

    if (!is_string($authorization_token['access_token']) && !is_string($authorization_token['user_id']))
    {
      return "Authorization token isn't of the type string.";
    }

    if ($authorization_token['expires_in'] < time())
    {
      return 'Authorization token expired.';
    }

    $decrypted_user_id = $this->decrypt_token($authorization_token['user_id'], $public_key['key']);
    $get_user_id = substr($decrypted_user_id, -1);
    $user_id = filter_var($get_user_id, FILTER_SANITIZE_STRING);

    if (!in_array($user_id, VALID_USERS))
    {
      return 'Invalid user ID.';
    }

    $response = [
      'response_type' => 'verified_token',
      'response_value' => true
    ];

    return $response;
  }

  private function get_private_key()
  {
    $private_key = file_get_contents('./keys/private.key');

    return $private_key;
  }

  private function get_public_key($private_key)
  {
    // create public key from resource
    $res = openssl_pkey_get_private($private_key);
    $public_key = openssl_pkey_get_details($res);

    return $public_key;
  }

  private function decrypt_token($token, $public_key)
  {
    // decode must be done before spliting
    $token = str_split(base64_decode($token), 256);
    $decrypted_token = '';

    foreach ($token as $chunk)
    {
      $decrypted_chunk = '';

      if (!openssl_public_decrypt($chunk, $decrypted_chunk, $public_key, OPENSSL_PKCS1_PADDING))
      {
        return false;
      }

      $decrypted_token .= $decrypted_chunk;
    }

    return $decrypted_token;
  }

  private function decode_token_structure($array)
  {
    return json_decode(base64_decode($array), true);
  }
}
