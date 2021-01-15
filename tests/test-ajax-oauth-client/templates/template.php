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
    <title>OAuth Client Application</title>
  </head>
  <body>

    <header class="header-container">
      <nav class="header-items">
        <a class="nav-item" href="/">
          <img id="logo" src="public/img/logo_davi.png" alt="logo">
        </a>
        <a class="nav-item" href="#">A propos</a>
        <a class="nav-item" href="#">Contact</a>
      </nav>
    </header>

    <div class="columns-container">
      <main class="left-column">
        <h2>Retorik The Emotional AI Platform</h2>
        <p>Pour créer des experts digitaux et ainsi être plus proche des utilisateurs, Davi a développé une IA adaptée aux besoins des entreprises qui réalisent des tâches complexes au service des humains.</p>

        <h2>S'IDENTIFIER</h2>
        <input id="username" type="text" name="username" value="username" onfocus="this.value = '';" onblur="if (this.value == '') {this.value = 'username';}">
        <input type="password" name="password" value="password" onfocus="this.value = '';" onblur="if (this.value == '') {this.value = 'password';}">
        <button type="button" name="request" value="POST">
          ACCÉDER
        </button>
      </main>

      <aside class="right-column">
        <h2>NOTRE MÉTIER</h2>
        <p>DAVI est un éditeur de logiciels en mode SaaS qui dispose des expertises dans les domaines de l’IA, de l’Affective Computing et des IHM.</p>
        <P>Pour mener à bien ses missions, DAVI dispose de compétences en ingénierie cognitive, en développement d’applications logicielles et en développement 3D.</p>
      </aside>
    </div>

    <div class="columns-container">
      <main class="services-column">
        <a class="service-link" href="">Dashboard</a>
        <a id="serviceIA" class="service-link" href="">Services IA</a>
        <a class="service-link" href="">NPL OWL</a>
        <a class="service-link" href="">NPL DEEP</a>
        <a class="service-link" href="">FAQ</a>
        <a class="service-link" href="">TTS</a>
      </main>

      <aside class="activities-column">
        <h2 class="activities-title">ACTIVITÉS RÉCENTES</h2>
        <p class="activities-message">Aucune activité récente n'est disponible.</p>
      </aside>
    </div>

    <footer class="footer-container">
      <p class="footer-text">Copyright © <a class="footer-text" href="https://davi.ai">DAVI</a> The Humanizers • 2020</p>
    </footer>

    <!-- <script src="public/js/script.js"></script> -->
  </body>
</html>
