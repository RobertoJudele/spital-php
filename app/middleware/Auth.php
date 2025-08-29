<?php
class Auth
{
    public static function check()
    {
        return isset($_SESSION['user_id']);
    }
    public static function hasRole($role)
    {
        global $pdo;

        $sql = 'Select name from user_roles where id=:role_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['role_id' => $_SESSION['role_id']]);

        $userRole = $stmt->fetchColumn();
        return $userRole === $role;
    }
    public static function requireRole($role)
    {
        if (!self::check() || !self::hasRole($role)) {
            $_SESSION['error'] = 'Access denied';
            require_once 'app/views/auth/login.php';
            exit();
        }
    }
}
