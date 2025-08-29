<?php
require_once 'app/models/User.php';

$users = User::getAllUsers();

echo '<p>Users:</p>';

foreach ($users as $user) {
    echo '<pre>';
    print_r($user);
    echo '</pre>';
}

?>
