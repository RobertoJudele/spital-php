<?php
require_once 'config/pdo.php';

class User
{
    public static function getAllUsers()
    {
        global $pdo;

        $sql = 'SELECT * FROM users';
        $stmt = $pdo->query($sql);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $users;
    }

    public static function getUser()
    {
        global $pdo;
        $sql = 'select * from users where id=:user_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $_GET['id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getUserByEmail($email)
    {
        global $pdo;
        $sql = 'select * from users where email=:email';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
