<?php

class IndexController
{

  public static function route_requests($uri, $request_method)
  {
    $valid_uris = ['', 'request_token', 'access_token', 'refresh_token', 'revoke_token'];

    if (in_array($uri, $valid_uris))
    {
      if ($uri === '' && $request_method === 'GET')
      {
        UserController::login_form();
      }
      elseif ($uri === 'request_token' && $request_method === 'POST')
      {
        CurlController::token_request();
      }
      else
      {
        if ((new JWTController)->verify(null, $uri) && $request_method === 'GET')
        {
          if ($uri === 'access_token')
          {
            CurlController::access_token_request();
          }
          elseif ($uri === 'refresh_token')
          {
            CurlController::refresh_token_request();
          }
          elseif ($uri === 'revoke_token')
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
