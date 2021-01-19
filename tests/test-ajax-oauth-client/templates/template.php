<?php
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
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <title>OAuth Client Application</title>
  </head>
  <body>

    <header class="header-container">
      <div class="header-logo">
        <a class="nav-item" href="/">
          <img id="logo" src="public/img/logo_davi.png" alt="logo">
        </a>
      </div>

      <nav class="header-items">
        <a class="nav-item" href="#">A propos</a>
        <a class="nav-item" href="#">Contact</a>
        <button id="logoutButton" class="hidden nav-item" type="button" name="revoke" value="POST" onclick="request(this.name, this.value);">
          Déconnexion
        </button>
        <a class="nav-item" href="#" style="font-size:30px;cursor:pointer" onclick="openNav()">&#9776;</a>
      </nav>

      <div id="mySidenav" class="sidenav">
        <a href="#" class="closebtn" onclick="closeNav()">&times;</a>
        <a class="sidenav-item" href="#">A propos</a>
        <a class="sidenav-item" href="#">Contact</a>
        <a class="sidenav-item" href="#">Mon compte</a>
        <button class="sidenav-item" type="button" name="revoke" value="POST" onclick="request(this.name, this.value);">
          Déconnexion
        </button>
      </div>
    </header>

    <!-- display view -->
    <div class="columns-container"></div>

    <footer class="footer-container">
      <p class="footer-text">Copyright © <a class="footer-text" href="https://davi.ai">DAVI</a> The Humanizers • 2020</p>
    </footer>

    <script src="public/js/script.js"></script>
  </body>
</html>
