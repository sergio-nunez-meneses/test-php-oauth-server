<?php

class ServicesView
{

  public static function display()
  {
    ob_start();
    ?>
    
    <!-- HTML goes here -->
    
    <?php
    $response['html'] = ob_get_contents();
    ob_clean();
    return $response;
  }
}
