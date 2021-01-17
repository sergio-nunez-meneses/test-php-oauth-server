<?php
chdir('..');
require 'include/class_autoloader.php';

IndexController::request_router($_POST['request'], $_POST);
