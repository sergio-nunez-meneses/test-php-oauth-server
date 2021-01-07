<?php

class IndexController
{

  public static function route_requests($uri, $request_method)
  {
    $valid_uris = ['auth', 'request_token', 'verify_token', 'access_token', 'refresh_token', 'revoke_token'];

    if (in_array($uri[1], $valid_uris) && in_array($uri[2], $valid_uris))
    {
      $path = $uri[1];
      $endpoints = $uri[2];

      if ($path === 'auth')
      {
        if ($endpoints === 'request_token' && $request_method === 'POST')
        {
          CurlController::token_request();
        }
        elseif ($endpoints === 'verify_token' && $request_method === 'GET')
        {
          CurlController::verify_token_request();
        }
        elseif ((new JWTController)->verify($endpoints) && $request_method === 'GET')
        {
          if ($endpoints === 'access_token')
          {
            CurlController::access_token_request();
          }
          elseif ($endpoints === 'refresh_token')
          {
            CurlController::refresh_token_request();
          }
          elseif ($endpoints === 'revoke_token')
          {
            CurlController::revoke_token_request();
          }
        }
      }
    }
    else
    {
      echo 'Page ' . strtoupper($uri[1]) . ': Not found.';
    }
  }
}
