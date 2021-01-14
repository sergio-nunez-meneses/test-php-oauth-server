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
    <link rel="stylesheet" href="public/css/style.css">
    <title>OAuth Server Test</title>
  </head>
  <body>

    <header id="mainHeader">
      <nav id="headerLinks">
        <a class="navLink" href="/">
          <img src="public/img/logo_davi.png" alt="logo">
        </a>
        <a class="navLink" href="">A propos</a>
        <a class="navLink" href="">Contact</a>
      </nav>
    </header>

    <div id=column>
      <main id="content">
        <h2>Retorik The Emotional AI Platform</h2>
        <p>Pour créer des experts digitaux et ainsi être plus proche des utilisateurs, Davi a développé une IA adaptée aux besoins des entreprises qui réalisent des tâches complexes au service des humains.</p>

        <input id="username" class="login-input" type="text" name="username" value="username" onfocus="this.value = '';" onblur="if (this.value == '') {this.value = 'username';}">
        <input class="login-input" type="password" name="password" value="password" onfocus="this.value = '';" onblur="if (this.value == '') {this.value = 'password';}">
      </main>

      <aside id="sidebar">
        <h2>NOTRE MÉTIER</h2>
        <p>DAVI est un éditeur de logiciels en mode SaaS qui dispose des expertises dans les domaines de l’IA, de l’Affective Computing et des IHM.</p>
        <P>Pour mener à bien ses missions, DAVI dispose de compétences en ingénierie cognitive, en développement d’applications logicielles et en développement 3D.</p>
      </aside>
    </div>

    <footer id="mainFooter">
      <p class="footer-text">Copyright © <a class="footer-text" href="https://davi.ai">davi</a> 2015 - 2020</p>
    </footer>

    <!-- <script src="public/js/script.js"></script> -->
  </body>
</html>
