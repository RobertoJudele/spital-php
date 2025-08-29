<?php
require_once 'config/config.php';
try {
    $pdo = new PDO(
        'mysql:host=' .
            $config['db_host'] .
            ';port=3306;dbname=' .
            $config['db_name'],
        $config['db_user'],
        $config['db_password'],
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Database connection failed: ' . $e->getMessage();
}
?>
