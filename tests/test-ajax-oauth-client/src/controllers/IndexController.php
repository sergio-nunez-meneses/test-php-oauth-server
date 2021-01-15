<?php

class IndexController
{

  public static function query_router($query, $inputs = false)
  {
    if ($query === 'login')
    {
      $response = AuthenticationView::display();
    }
    elseif ($query === 'services')
    {
      $response = ServicesView::display();
    }
    else
    {
      $response = AuthenticationView::display();
    }

    require 'templates/template.php';
  }
}
