<?php

class ServicesView
{

  public static function display()
  {
    ob_start();
    ?>

    <aside class="services-column">
      <a class="service-link" href="">Dashboard</a>
      <a id="serviceIA" class="service-link" href="">Services IA</a>
      <a class="service-link" href="">NPL OWL</a>
      <a class="service-link" href="">NPL DEEP</a>
      <a class="service-link" href="">FAQ</a>
      <a class="service-link" href="">TTS</a>
    </aside>

    <main class="activities-column">
      <h2 class="activities-title">ACTIVITÉS RÉCENTES</h2>
      <p class="activities-message">Aucune activité récente n'est disponible.</p>
    </main>

    <?php
    $response['html'] = ob_get_contents();
    ob_clean();
    return $response;
  }
}
