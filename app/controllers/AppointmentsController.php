<?php
require_once 'app/middleware/Auth.php';
require_once 'app/middleware/Csrf.php';

class AppointmentsController
{
    public static function request()
    {
        Auth::requireRole('pacient');
        global $pdo;
        if (!$pdo) {
            throw new RuntimeException('PDO not initialized');
        }
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $uid = (int) ($_SESSION['user_id'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // folosim doctors.id ca value în <option>
            $sql = "SELECT d.id AS doctor_id, u.first_name, u.last_name, u.email
                    FROM doctors d
                    JOIN users u ON u.id = d.user_id
                    ORDER BY u.last_name, u.first_name";
            $doctors = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            require 'app/views/appointments/request.php';
            return;
        }

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $_SESSION['error'] = 'Invalid CSRF token.';
            header("Location: {$base}/index.php?r=spital/appointments/request");
            exit();
        }

        // mapăm user curent -> patients.id
        $stmtPid = $pdo->prepare(
            'SELECT id FROM patients WHERE user_id = :uid LIMIT 1',
        );
        $stmtPid->execute(['uid' => $uid]);
        $patientId = (int) ($stmtPid->fetchColumn() ?: 0);
        if ($patientId <= 0) {
            $_SESSION['error'] = 'No patient profile linked to this account.';
            header("Location: {$base}/index.php?r=spital/appointments/request");
            exit();
        }

        $doctorId =
            filter_input(INPUT_POST, 'doctor_id', FILTER_VALIDATE_INT) ?: 0; // acesta este doctors.id
        $date = trim($_POST['date'] ?? '');
        if ($doctorId <= 0 || $date === '') {
            $_SESSION['error'] = 'Doctor and date are required.';
            header("Location: {$base}/index.php?r=spital/appointments/request");
            exit();
        }

        // Inserăm cu patients.id și doctors.id
        $stmt = $pdo->prepare(
            'INSERT INTO appointments (patient_id, doctor_id, date) VALUES (:pid, :did, :date)',
        );
        $ok = $stmt->execute([
            'pid' => $patientId,
            'did' => $doctorId,
            'date' => $date,
        ]);
        $_SESSION[$ok ? 'msg' : 'error'] = $ok
            ? 'Appointment request submitted.'
            : 'Failed to create appointment.';
        header("Location: {$base}/index.php?r=spital/patients/dashboard");
        exit();
    }

    public static function approve()
    {
        Auth::requireRole('admin');
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            !Csrf::verify($_POST['csrf_token'] ?? null)
        ) {
            $_SESSION['error'] = 'Invalid request.';
            self::backToAdmin();
            return;
        }
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
        global $pdo;
        $stmt = $pdo->prepare(
            'UPDATE appointments SET status = 1 WHERE id = :id',
        );
        $ok = $stmt->execute(['id' => $id]);
        $_SESSION[$ok ? 'msg' : 'error'] = $ok
            ? 'Appointment approved.'
            : 'Failed to approve.';
        self::backToAdmin();
    }

    public static function reject()
    {
        Auth::requireRole('admin');
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            !Csrf::verify($_POST['csrf_token'] ?? null)
        ) {
            $_SESSION['error'] = 'Invalid request.';
            self::backToAdmin();
            return;
        }
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
        global $pdo;
        $stmt = $pdo->prepare(
            'UPDATE appointments SET status = 0 WHERE id = :id',
        );
        $ok = $stmt->execute(['id' => $id]);
        $_SESSION[$ok ? 'msg' : 'error'] = $ok
            ? 'Appointment rejected.'
            : 'Failed to reject.';
        self::backToAdmin();
    }

    public static function show()
    {
        Auth::requireRole('pacient');
        global $pdo;
        if (!$pdo) {
            throw new RuntimeException('PDO not initialized');
        }

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
        $uid = (int) ($_SESSION['user_id'] ?? 0);

        // user -> patients.id
        $stmtPid = $pdo->prepare(
            'SELECT id FROM patients WHERE user_id = :uid LIMIT 1',
        );
        $stmtPid->execute(['uid' => $uid]);
        $patientId = (int) ($stmtPid->fetchColumn() ?: 0);

        $sql = "SELECT a.*, du.first_name AS doctor_first, du.last_name AS doctor_last, du.email AS doctor_email
                FROM appointments a
                JOIN doctors d ON d.id = a.doctor_id
                JOIN users du ON du.id = d.user_id
                WHERE a.id = :id AND a.patient_id = :pid";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id, 'pid' => $patientId]);
        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$appointment) {
            http_response_code(404);
            require 'app/views/404.php';
            return;
        }

        require 'app/views/appointments/show.php';
    }

    private static function backToAdmin(): void
    {
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        header("Location: {$base}/index.php?r=spital/admin/dashboard");
        exit();
    }
}
