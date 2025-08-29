<?php
$cookieParams = [
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']), // true în producție HTTPS
    'httponly' => true,
    'samesite' => 'Lax', // 'Strict' pentru acțiuni sensibile
];
session_set_cookie_params($cookieParams);
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// XSS/security headers
header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer-when-downgrade');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
header(
    "Content-Security-Policy: default-src 'self'; img-src 'self' data: https://www.google.com https://www.gstatic.com; script-src 'self' 'unsafe-inline' https://www.google.com https://www.gstatic.com https://www.recaptcha.net; style-src 'self' 'unsafe-inline'; frame-src https://www.google.com https://www.recaptcha.net; connect-src 'self' https://www.google.com https://www.gstatic.com;",
);

// helper escape disponibil peste tot
require_once __DIR__ . '/app/helpers/esc.php';

require_once __DIR__ . '/config/pdo.php';
require_once __DIR__ . '/config/routes.php';

$router = new Router();
$router->direct();
