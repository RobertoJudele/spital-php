<?php
require_once 'config/recaptcha.php';
require_once 'app/models/User.php';
require_once 'app/middleware/Csrf.php';

class AuthController
{
    private static function verifyRecaptcha(?string $token): bool
    {
        if (!$token) {
            return false;
        }
        $query = http_build_query([
            'secret' => RECAPTCHA_SECRET_KEY,
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
        $resp = @file_get_contents(
            "https://www.google.com/recaptcha/api/siteverify?$query",
        );
        if ($resp === false) {
            return false;
        }
        $data = json_decode($resp, true);
        return !empty($data['success']);
    }

    public static function auth()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require_once 'app/views/auth/login.php';
            return;
        }

        // 1) Verifică metoda + CSRF
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            !Csrf::verify($_POST['csrf_token'] ?? null)
        ) {
            $_SESSION['error'] = 'Invalid request.';
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
            header("Location: {$base}/index.php?r=spital/auth/login");
            exit();
        }

        // 2) reCAPTCHA
        // $token = $_POST['g-recaptcha-response'] ?? '';
        // if (!self::verifyRecaptcha($token)) {
        //     $_SESSION['error'] = 'reCAPTCHA failed.';
        //     $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        //     header("Location: {$base}/index.php?r=spital/auth/login");
        //     exit();
        // }

        $token = $_POST['g-recaptcha-response'] ?? '';
        $secret = RECAPTCHA_SECRET_KEY;

        $verify = file_get_contents(
            'https://www.google.com/recaptcha/api/siteverify?secret=' .
                urlencode($secret) .
                '&response=' .
                urlencode($token) .
                '&remoteip=' .
                $_SERVER['REMOTE_ADDR'],
        );
        $res = json_decode($verify, true);
        if (empty($res['success'])) {
            $_SESSION['error'] = 'reCAPTCHA failed.';
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
            header("Location: {$base}/index.php?r=spital/auth/login");
            exit();
        }

        // 3) Autentificare (folosește parole hash dacă e posibil)
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $user = User::getUserByEmail($email);
        $ok = false;
        if ($user && isset($user['password'])) {
            $hash = (string) $user['password'];
            $ok =
                str_starts_with($hash, '$2y$') ||
                str_starts_with($hash, '$argon2')
                    ? password_verify($password, $hash)
                    : hash_equals($hash, $password); // fallback dacă ai parole vechi în clar
        }

        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if ($ok) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['role_id'] = (int) $user['role_id'];

            // află numele rolului și salvează în sesiune
            global $pdo;
            $stmt = $pdo->prepare('SELECT name FROM user_roles WHERE id = :id');
            $stmt->execute(['id' => (int) $user['role_id']]);
            $_SESSION['role_name'] = (string) ($stmt->fetchColumn() ?: '');

            // redirecționează pe rol
            $role = $_SESSION['role_name'];
            $dest = 'spital/home/index';
            if ($role === 'admin') {
                $dest = 'spital/admin/dashboard';
            }
            if ($role === 'doctor') {
                $dest = 'spital/doctor/dashboard';
            }
            if ($role === 'pacient') {
                $dest = 'spital/patients/dashboard';
            }

            header("Location: {$base}/index.php?r={$dest}");
            exit();
        }
        $_SESSION['error'] = 'Invalid email or password';
        header("Location: {$base}/index.php?r=spital/auth/login");
        exit();
    }
    public static function logout()
    {
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            !Csrf::verify($_POST['csrf_token'] ?? null)
        ) {
            http_response_code(405);
            $_SESSION['error'] = 'Invalid logout request!';
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
            header("Location: {$base}/index.php?r=spital/auth/login");
            exit();
        }
        $_SESSION = [];

        if (ini_get('session.user_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'] ?: '/',
                'domain' => $params['domain'] ?: '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        session_destroy();
        session_regenerate_id(true);
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        header("Location: {$base}/index.php?r=spital/auth/login");
        exit();
    }
}
