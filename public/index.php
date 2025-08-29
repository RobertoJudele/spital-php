<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/pdo.php';
require_once __DIR__ . '/../config/routes.php';

$router = new Router();
$router->direct();
?>

<h1>aloooo</h1>