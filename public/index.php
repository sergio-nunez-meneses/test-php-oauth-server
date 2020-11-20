<?php require_once('../include/class_autoloader.php'); ?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <title>OAuth Server Test</title>
  </head>
  <body>

    <?php
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = explode('/', $uri);
    // $request_method = $_SERVER['REQUEST_METHOD'];

    if ($uri[1] === '') {
      UserController::login_form();
    } else {
      throw new \Exception('Page ' . $uri[1] . ': Not found.');
    }
    ?>

  </body>
</html>
