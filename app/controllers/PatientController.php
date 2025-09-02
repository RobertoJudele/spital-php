<?php
require_once 'app/models/Patients.php';
require_once 'app/middleware/Auth.php';

class PatientController
{
    public static function index()
    {
        $patients = Patient::getAllPatients();
        require_once 'app/views/patients/index.php';
    }
    public static function show()
    {
        $patient_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$patient_id) {
            $_SESSION['error'] = 'Invalid patient ID';
            require_once 'app/views/404.php';
            return;
        }
        $patient = Patient::getPatient($patient_id);
        if ($patient) {
            require_once 'app/views/patients/show.php';
        } else {
            $_SESSION['error'] = 'Patient not found';
            require_once 'app/views/404.php';
        }
    }
    public static function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require_once 'app/views/patients/create.php';
            return;
        }

        // Server-side validation
        $first = trim($_POST['first_name'] ?? '');
        $last = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass = (string) ($_POST['password'] ?? '');
        $cnp = trim($_POST['cnp'] ?? '');
        $blood = trim($_POST['blood_type'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $addr = trim($_POST['address'] ?? '');
        $all = trim($_POST['allergies'] ?? '');

        $errors = [];
        if ($first === '') {
            $errors[] = 'Prenume obligatoriu';
        }
        if ($last === '') {
            $errors[] = 'Nume obligatoriu';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email invalid';
        }
        if ($pass === '') {
            $errors[] = 'Parolă obligatorie';
        }
        if (!preg_match('/^\d{13}$/', $cnp)) {
            $errors[] = 'CNP invalid (trebuie 13 cifre)';
        }
        if ($blood === '') {
            $errors[] = 'Grupa sanguină obligatorie';
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode(' | ', $errors);
            require_once 'app/views/patients/create.php';
            return;
        }

        $data = [
            'first_name' => $first,
            'last_name' => $last,
            'email' => $email,
            'password' => password_hash($pass, PASSWORD_BCRYPT),
            'cnp' => $cnp,
            'phone' => $phone,
            'address' => $addr,
            'blood_type' => $blood,
            'allergies' => $all,
        ];

        $created = Patient::create($data);
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if ($created) {
            header("Location: {$base}/index.php?r=spital/auth/login");
            exit();
        }

        $_SESSION['error'] = 'Failed to create patient';
        require_once 'app/views/patients/create.php';
    }
    public static function edit()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Get the patient ID from the query string
            $patient_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$patient_id) {
                $_SESSION['error'] = 'Invalid patient ID';
                require_once 'app/views/404.php';
                return;
            }

            // Fetch the patient data
            $patient = Patient::getPatient($patient_id);
            if ($patient) {
                require_once 'app/views/patients/edit.php'; // Display the edit form
            } else {
                $_SESSION['error'] = 'Patient not found';
                require_once 'app/views/404.php';
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the patient ID from the form
            $patient_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$patient_id) {
                $_SESSION['error'] = 'Invalid patient ID';
                require_once 'app/views/404.php';
                return;
            }

            // Collect updated data from the form
            $data = [
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'email' => $_POST['email'],
                'password' => password_hash(
                    $_POST['password'],
                    PASSWORD_BCRYPT,
                ), // Hash the password
                'user_id' => $_POST['user_id'],
                'cnp' => $_POST['cnp'],
                'phone' => $_POST['phone'],
                'address' => $_POST['address'],
                'blood_type' => $_POST['blood_type'],
                'allergies' => $_POST['allergies'],
            ];

            // Call the model to update the patient
            $updated_patient = Patient::update($patient_id, $data);
            if ($updated_patient) {
                header('Location: /spital/patients/index'); // Redirect to the patients list
                exit();
            } else {
                $_SESSION['error'] = 'Failed to update patient';
                require_once 'app/views/404.php';
            }
        }
    }
    public static function delete()
    {
        $patient_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$patient_id) {
            $_SESSION['error'] = 'Invalid patient id';
            require_once 'app/views/404.php';
        }
        $deleted = Patient::delete($patient_id);
        if ($deleted) {
            header('Location: /spital/patients/index');
        } else {
            $_SESSION['error'] = 'Failed to delete patient';
            require_once 'app/views/404.php';
        }
    }
    public static function dashboard()
    {
        Auth::requireRole('pacient');

        global $pdo;
        if (!$pdo) {
            throw new RuntimeException('PDO not initialized');
        }

        $uid = (int) ($_SESSION['user_id'] ?? 0); // user.id al pacientului curent
        $stmtPid = $pdo->prepare(
            'SELECT id FROM patients WHERE user_id = :uid LIMIT 1',
        );
        $stmtPid->execute(['uid' => $uid]);
        $patientId = (int) ($stmtPid->fetchColumn() ?: 0);
        if ($uid <= 0) {
            $_SESSION['error'] = 'Not authenticated';
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
            header("Location: {$base}/index.php?r=spital/auth/login");
            exit();
        }

        // Appointments: folosim patients.user_id și doctors.user_id
        $stmt = $pdo->prepare("
            SELECT a.id, a.status, a.date,
                   du.first_name AS doctor_first, du.last_name AS doctor_last
            FROM appointments a
            JOIN doctors d ON d.id = a.doctor_id
            JOIN users du ON du.id = d.user_id
            WHERE a.patient_id = :pid
            ORDER BY a.date DESC
        ");
        $stmt->execute(['pid' => $patientId]);
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Medical records
        $stmt = $pdo->prepare("
            SELECT mr.id, mr.initial_observations,
                   du.first_name AS doctor_first, du.last_name AS doctor_last
            FROM medical_record mr
            LEFT JOIN users du ON du.id = mr.doctor_id
            WHERE mr.patient_id = :uid
            ORDER BY mr.id DESC
        ");
        $stmt->execute(['uid' => $uid]);
        $medicalRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Consultations (c.doctor_id -> doctors.id; filtrăm după mr.patient_id)
        $stmt = $pdo->prepare("
            SELECT c.id, c.consultation_date, c.notes, c.diagnosis,
                   du.first_name AS doctor_first, du.last_name AS doctor_last,
                   c.medical_record_id
            FROM consultations c
            JOIN medical_record mr ON mr.id = c.medical_record_id
            JOIN doctors dd ON dd.id = c.doctor_id
            JOIN users du ON du.id = dd.user_id
            WHERE mr.patient_id = :pid
            ORDER BY c.consultation_date DESC, c.id DESC
        ");
        $stmt->execute(['pid' => $patientId]);
        $consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prescriptions
        $stmt = $pdo->prepare("
            SELECT p.id, p.prescription,
                   du.first_name AS doctor_first, du.last_name AS doctor_last
            FROM prescriptions p
            JOIN doctors dd ON dd.id = p.doctor_id
            JOIN users du ON du.id = dd.user_id
            WHERE p.patient_id = :pid
            ORDER BY p.id DESC
        ");
        $stmt->execute(['pid' => $patientId]);
        $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Ultima internare / cameră
        $stmt = $pdo->prepare("
            SELECT a.id, a.admission_date, a.discharge_date,
                   r.room_number, r.capacity, dep.name AS department
            FROM admissions a
            JOIN rooms r ON r.id = a.room_id
            LEFT JOIN departments dep ON dep.id = r.department_id
            WHERE a.patient_id = :pid
            ORDER BY a.admission_date DESC
            LIMIT 1
        ");

        try {
            $stmt->execute(['pid' => $patientId]);
            $lastAdmission = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable $e) {
            // dacă schema ta are alt nume de coloană, doar nu afișăm secțiunea
            $lastAdmission = null;
        }

        require_once 'app/views/patients/dashboard.php';
    }
}
?>
