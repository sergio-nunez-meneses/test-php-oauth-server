<?php

class IndexController
{

  public static function route_requests($uri, $request_method)
  {
    $valid_uris = ['', 't_request', 'at_request', 'rt_request'];

    if (in_array($uri, $valid_uris))
    {
      if ($uri === '' && $request_method === 'GET')
      {
        UserController::login_form();
      }
      elseif ($uri === 't_request' && $request_method === 'POST')
      {
        CurlController::token_request();
      }
      else
      {
        if ($request_method === 'GET' && (new JWTController)->verify())
        {
          if ($uri === 'at_request')
          {
            CurlController::access_token_request();
          }
          elseif ($uri === 'rt_request')
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
