<?php

spl_autoload_register('class_autoloader');

function class_autoloader($class_name) {
  $file_name = strtolower($class_name);
  $path = "/../src/$file_name.php";

  if (!file_exists($path)) {
    throw new \Exception('Loaded class not found');
  }

  require_once($path);
}
