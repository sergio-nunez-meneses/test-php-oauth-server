<?php
require 'include/class_autoloader.php';

// handle cross origin resource sharing (CORS)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: OPTIONS, GET, POST');
header('Access-Control-Max-Age: 3600');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Sergio NUNEZ MENESES">
    <link rel="stylesheet" href="public/css/normalize.css">
    <link rel="stylesheet" href="public/css/style.css">
    <title>Authentication Token Request</title>
  </head>
  <body>

    <header>
      <section class="header-container">
        <img class="header-logo" src="public/img/logo_davi.png">
        <h1 class="header-title">Emotional AI Web Services</h1>
      </section>
    </header>


    <main class="main-container">
      <!-- loading spinner -->
      <section id="overlay" class="hidden spinner-overlay">
        <div id="spinner" class="spinner loading">
          <p id="spinnerText" class="spinner-text"></p>
        </div>
      </section>

			<section id="contentContainer" class="login-container"></section>
		</main>

    <footer>
      <section class="footer-container">
        <p class="footer-text">Copyright © <a class="footer-text" href="https://davi.ai/en/home/">davi</a> 2015 - 2020</p>
      </section>
		</footer>

    <script src="public/js/script.js"></script>
  </body>
</html>
