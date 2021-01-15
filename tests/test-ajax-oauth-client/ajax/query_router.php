<?php
chdir('..');
require 'include/class_autoloader.php';

IndexController::query_router($_POST['query'], $_POST);
