<?php

class IndexController
{

  public static function request_router($request, $inputs = false)
  {
    if ($request === 'login')
    {
      $response = AuthenticationView::display();
    }
    elseif ($request === 'services')
    {
      $response = ServicesView::display();
    }
    elseif ($request === 'request')
    {
      $response = ResponseController::request_token($inputs);
    }
    elseif ($request === 'validate')
    {
      $response = ResponseController::validate_token();
    }
    else
    {
      $response = AuthenticationView::display();
    }

    echo $response;
  }
}
