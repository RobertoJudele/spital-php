<?php
require_once 'app/middleware/Auth.php';
require_once 'app/middleware/Csrf.php';

class AdminController
{
    public static function dashboard()
    {
        Auth::requireRole('admin');
        global $pdo;

        // Doctors
        $sqlDoctors = "SELECT u.id, u.first_name, u.last_name, u.email,
                              d.department, d.specialization, d.grade
                       FROM users u
                       INNER JOIN doctors d ON d.user_id = u.id
                       ORDER BY u.last_name, u.first_name";
        $doctors = $pdo->query($sqlDoctors)->fetchAll(PDO::FETCH_ASSOC);

        // Patients
        $sqlPatients = "SELECT u.id, u.first_name, u.last_name, u.email
                        FROM users u
                        INNER JOIN patients p ON p.user_id = u.id
                        ORDER BY u.last_name, u.first_name";
        $patients = $pdo->query($sqlPatients)->fetchAll(PDO::FETCH_ASSOC);

        // Pending appointments pentru aprobare
        $sql = "SELECT a.id, a.date, a.status,
                       pu.first_name AS patient_first, pu.last_name AS patient_last,
                       du.first_name AS doctor_first,  du.last_name AS doctor_last
                FROM appointments a
                JOIN users pu ON pu.id = a.patient_id
                JOIN users du ON du.id = a.doctor_id
                WHERE a.status IS NULL
                ORDER BY a.date ASC";
        $pendingAppointments = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        // Departments (pentru dropdown)
        $departments = $pdo
            ->query('SELECT id, name FROM departments ORDER BY name')
            ->fetchAll(PDO::FETCH_ASSOC);

        // Rooms list
        $rooms = $pdo
            ->query(
                "
            SELECT r.id, r.room_number, r.capacity, r.description, dep.name AS department
            FROM rooms r
            JOIN departments dep ON dep.id = r.department_id
            ORDER BY r.room_number
        ",
            )
            ->fetchAll(PDO::FETCH_ASSOC);
        require_once 'app/views/admin/dashboard.php';
    }
    public static function createRoom()
    {
        Auth::requireRole('admin');
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            !Csrf::verify($_POST['csrf_token'] ?? null)
        ) {
            $_SESSION['error'] = 'Invalid request.';
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
            header("Location: {$base}/index.php?r=spital/admin/dashboard");
            exit();
        }

        $departmentId =
            filter_input(INPUT_POST, 'department_id', FILTER_VALIDATE_INT) ?: 0;
        $roomNumber = trim($_POST['room_number'] ?? '');
        $capacity =
            filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT) ?: 0;
        $description = trim($_POST['description'] ?? '');

        if ($departmentId <= 0 || $roomNumber === '' || $capacity <= 0) {
            $_SESSION['error'] =
                'Department, room number and capacity are required.';
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
            header("Location: {$base}/index.php?r=spital/admin/dashboard");
            exit();
        }

        global $pdo;
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO rooms (department_id, room_number, capacity, description) VALUES (:dep, :num, :cap, :desc)',
            );
            $ok = $stmt->execute([
                'dep' => $departmentId,
                'num' => $roomNumber,
                'cap' => $capacity,
                'desc' => $description !== '' ? $description : null,
            ]);
            $_SESSION[$ok ? 'msg' : 'error'] = $ok
                ? 'Room created.'
                : 'Failed to create room.';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'DB error: ' . $e->getMessage();
        }

        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        header("Location: {$base}/index.php?r=spital/admin/dashboard");
        exit();
    }
}
