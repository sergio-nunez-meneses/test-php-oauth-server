<?php

class UserView
{

  public static function display()
  {
    ob_start();
    ?>
    <div class="container my-5">
      <div class="w-50 mx-auto my-5">
        <fieldset>
          <legend class="text-uppercase">S'identifier</legend>
          <form action="/" method="POST">
            <div class="form-group">
              <input class="form-control" type="text" name="username" placeholder="nom d'utilisateur" required>
            </div>
            <div class="form-group">
              <input class="form-control" type="text" name="license" placeholder="numéro de license" required>
            </div>
            <div class="form-group">
              <input class="form-control" type="password" name="password" placeholder="mot de passe" required>
            </div>
            <div class="form-group">
              <button class="w-100 btn btn-md bg-success text-white text-uppercase" type="submit" name="login">Envoyer</button>
            </div>
          </form>
        </fieldset>
      </div>
    </div>
    <?php
    $content = ob_get_clean();
    require('../templates/template.php');
  }
}
