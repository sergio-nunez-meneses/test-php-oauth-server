<?php

class AuthenticationView
{

  public static function display()
  {
    ob_start();
    ?>

    <main class="left-column">
      <div class="retorik-container">
        <h2 class="retorik-title">Retorik The Emotional AI Platform</h2>
        <p class="retorik-text">Pour créer des experts digitaux et ainsi être plus proche des utilisateurs, Davi a développé une IA adaptée aux besoins des entreprises qui réalisent des tâches complexes au service des humains.</p>
      </div>

      <div class="login-container">
        <h2 class="login-title">S'IDENTIFIER</h2>
        <input id="username" class="login-input" type="text" name="username" value="nom d'utilisateur" onfocus="this.value = '';" onblur="if (this.value == '') { this.value = 'nom d\'utilisateur'; }">
        <input class="login-input" type="password" name="password" value="mot de passe" onfocus="this.value = '';" onblur="if (this.value == '') { this.value = 'mot de passe'; }">
        <button class="request-button" type="button" name="request" value="POST">
          ACCÉDER
        </button>

        <div class="status-container"></div>
    </main>

    <aside class="right-column">
      <div class="about-container">
        <h2 class="about-title">NOTRE MÉTIER</h2>
        <p class="about-text">DAVI est un éditeur de logiciels en mode SaaS qui dispose des expertises dans les domaines de l’IA, de l’Affective Computing et des IHM.</p>
        <P class="about-text">Pour mener à bien ses missions, DAVI dispose de compétences en ingénierie cognitive, en développement d’applications logicielles et en développement 3D.</p>
      </div>
    </aside>

    <?php
    $response['html'] = ob_get_contents();
    $response['callback'] = 'display';

    ob_clean();

    return json_encode($response);
  }
}
