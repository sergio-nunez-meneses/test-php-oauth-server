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
    if ($_SERVER['REQUEST_URI'] === '/') {
      UserController::login_form();
    } else {
      throw new \Exception($_SERVER['REQUEST_URI'] . ': Not found.');
    }
    ?>

  </body>
</html>
