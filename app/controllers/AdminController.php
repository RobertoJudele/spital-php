<?php
require_once 'app/middleware/Auth.php';
require_once 'app/middleware/Csrf.php';
require_once 'app/models/Patients.php';

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

        // Pending appointments cu nume corecte
        $stmt = $pdo->query("
        SELECT a.id, a.date, a.status,
               pu.first_name AS patient_first, pu.last_name AS patient_last,
               du.first_name AS doctor_first, du.last_name AS doctor_last
        FROM appointments a
        JOIN patients p ON p.id = a.patient_id
        JOIN users pu   ON pu.id = p.user_id
        JOIN doctors d  ON d.id = a.doctor_id
        JOIN users du   ON du.id = d.user_id
        WHERE a.status IS NULL
        ORDER BY a.date ASC, a.id ASC
    ");
        $pendingAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    public static function editPatient()
    {
        Auth::requireRole('admin');
        global $pdo;

        $userId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
        if ($userId <= 0) {
            $_SESSION['error'] = 'Invalid patient id';
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
            header("Location: {$base}/index.php?r=spital/patients/index");
            exit();
        }

        $patient = Patient::getPatient($userId);
        if (!$patient) {
            $_SESSION['error'] = 'Patient not found';
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
            header("Location: {$base}/index.php?r=spital/patients/index");
            exit();
        }

        require_once 'app/views/admin/patient_edit.php';
    }

    public static function updatePatient()
    {
        Auth::requireRole('admin');
        global $pdo;

        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            !Csrf::verify($_POST['csrf_token'] ?? null)
        ) {
            $_SESSION['error'] = 'Invalid request.';
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
            header("Location: {$base}/index.php?r=spital/patients/index");
            exit();
        }

        $userId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
        $first = trim($_POST['first_name'] ?? '');
        $last = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass = (string) ($_POST['password'] ?? '');
        $cnp = trim($_POST['cnp'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $addr = trim($_POST['address'] ?? '');
        $blood = trim($_POST['blood_type'] ?? '');
        $all = trim($_POST['allergies'] ?? '');

        $errors = [];
        if ($userId <= 0) {
            $errors[] = 'Invalid id';
        }
        if ($first === '') {
            $errors[] = 'Prenumele este obligatoriu';
        }
        if ($last === '') {
            $errors[] = 'Numele este obligatoriu';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email invalid';
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode(' | ', $errors);
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
            header(
                "Location: {$base}/index.php?r=spital/admin/patients/edit&id={$userId}",
            );
            exit();
        }

        // Păstrează parola existentă dacă nu s-a introdus una nouă
        if ($pass === '') {
            $stmt = $pdo->prepare('SELECT password FROM users WHERE id = :id');
            $stmt->execute(['id' => $userId]);
            $hash = (string) $stmt->fetchColumn();
        } else {
            $hash = password_hash($pass, PASSWORD_BCRYPT);
        }

        $ok = Patient::update($userId, [
            'first_name' => $first,
            'last_name' => $last,
            'email' => $email,
            'password' => $hash,
            'cnp' => $cnp,
            'phone' => $phone,
            'address' => $addr,
            'blood_type' => $blood,
            'allergies' => $all,
        ]);

        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if ($ok) {
            $_SESSION['msg'] = 'Patient updated successfully';
            header("Location: {$base}/index.php?r=spital/patients/index");
        } else {
            $_SESSION['error'] = 'Failed to update patient';
            header(
                "Location: {$base}/index.php?r=spital/admin/patients/edit&id={$userId}",
            );
        }
        exit();
    }

    public static function deletePatient()
    {
        Auth::requireRole('admin');

        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            !Csrf::verify($_POST['csrf_token'] ?? null)
        ) {
            $_SESSION['error'] = 'Invalid request.';
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
            header("Location: {$base}/index.php?r=spital/patients/index");
            exit();
        }

        $userId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
        if ($userId <= 0) {
            $_SESSION['error'] = 'Invalid id';
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
            header("Location: {$base}/index.php?r=spital/patients/index");
            exit();
        }

        global $pdo;
        try {
            $pdo->beginTransaction();

            // patient_id din users.id
            $stmt = $pdo->prepare(
                'SELECT id FROM patients WHERE user_id = :uid LIMIT 1',
            );
            $stmt->execute(['uid' => $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                $pdo->rollBack();
                $_SESSION['error'] = 'Patient not found';
                $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
                header("Location: {$base}/index.php?r=spital/patients/index");
                exit();
            }
            $patientId = (int) $row['id'];

            // ștergere dependentă
            $pdo->prepare(
                'DELETE FROM consultations WHERE medical_record_id IN (SELECT id FROM medical_record WHERE patient_id = :pid)',
            )->execute(['pid' => $patientId]);
            $pdo->prepare(
                'DELETE FROM medical_record WHERE patient_id = :pid',
            )->execute(['pid' => $patientId]);
            $ok = $pdo
                ->prepare('DELETE FROM patients WHERE id = :pid')
                ->execute(['pid' => $patientId]);

            // ștergere utilizator
            $pdo->prepare('DELETE FROM users WHERE id = :uid')->execute([
                'uid' => $userId,
            ]);

            $pdo->commit();
            $_SESSION[$ok ? 'msg' : 'error'] = $ok
                ? 'Patient deleted.'
                : 'Failed to delete patient.';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['error'] = 'DB error: ' . $e->getMessage();
        }

        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        header("Location: {$base}/index.php?r=spital/patients/index");
        exit();
    }
}
