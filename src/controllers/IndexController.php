<?php

class IndexController
{

  public static function route_requests($uri, $request_method)
  {
    $path = $uri[1];
    $endpoints = $uri[2];
    $valid_uris = ['', 'auth', 'request_token', 'access_token', 'refresh_token', 'revoke_token'];

    if (in_array($path, $valid_uris) && in_array($endpoints, $valid_uris))
    {
      if ($path === '' && $request_method === 'GET')
      {
        UserController::login_form();
      }
      elseif ($path === 'auth')
      {
        if ($endpoints === 'request_token' && $request_method === 'POST')
        {
          CurlController::token_request();
        }
        elseif ((new JWTController)->verify(null, $endpoints) && $request_method === 'GET')
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
      throw new \Exception('Page ' . strtoupper($uri) . ': Not found.');
    }
  }
}
