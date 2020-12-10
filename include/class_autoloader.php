<?php

spl_autoload_register('class_autoloader');

function class_autoloader($class_name) {
  if (substr($class_name, -5) === 'Model') {
    $parent_folder = 'models';
  } elseif (substr($class_name, -10) === 'Controller') {
    $parent_folder = 'controllers';
  }

  if ($parent_folder === '') {
    $path = "./src/$class_name.php";
  } else {
    $path = "./src/$parent_folder/$class_name.php";
  }

  if (!file_exists($path)) {
    throw new \Exception('Loaded class not found');
  }

  require $path;
}
