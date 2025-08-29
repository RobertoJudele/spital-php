<?php
require_once 'app/middleware/Auth.php';
require_once 'app/middleware/Csrf.php';
require_once 'app/models/Doctor.php';

class DoctorController
{
    public static function index()
    {
        Auth::requireRole('admin');
        $doctors = Doctor::getAllDoctors();
        require_once 'app/views/doctors/index.php';
    }

    public static function show()
    {
        $doctor_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$doctor_id) {
            $_SESSION['error'] = 'Invalid patient id';
            require_once 'app/views/404.php';
            return;
        }
        $doctor = Doctor::getDoctor($doctor_id);
        if ($doctor) {
            require_once 'app/views/doctors/show.php';
        } else {
            $_SESSION['error'] = 'Doctor not found';
            require_once 'app/views/404.php';
        }
    }

    public static function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require_once 'app/views/doctors/create.php';
            return;
        }
        $data = [
            'first_name' => $_POST['first_name'] ?? null,
            'last_name' => $_POST['last_name'] ?? null,
            'email' => $_POST['email'] ?? null,
            'password' => password_hash(
                $_POST['password'] ?? '',
                PASSWORD_BCRYPT,
            ),
            'department' => $_POST['department'] ?? null,
            'grade' => $_POST['grade'] ?? null,
            'specialization' => $_POST['specialization'] ?? null,
        ];
        $created = Doctor::create($data);
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

        if ($created) {
            header("Location: {$base}/index.php?r=spital/doctors/index");
            exit();
        } else {
            $_SESSION['error'] = 'Failed to create doctor';
            require_once 'app/views/404.php';
        }
    }

    public static function delete()
    {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $csrf = $_POST['csrf_token'] ?? null;
    }

    public static function edit()
    {
        Auth::requireRole('admin');

        $id = isset($_GET['id'])
            ? (int) $_GET['id']
            : (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'Invalid doctor id.';
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
            header("Location: {$base}/index.php?r=spital/admin/dashboard");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $doctor = Doctor::find($id);
            if (!$doctor) {
                $_SESSION['error'] = 'Doctor not found.';
                $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
                header("Location: {$base}/index.php?r=spital/admin/dashboard");
                exit();
            }
            require_once 'app/views/doctors/edit.php';
            return;
        }

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $_SESSION['error'] = 'Invalid CSRF token.';
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
            header(
                "Location: {$base}/index.php?r=spital/doctors/edit&id={$id}",
            );
            exit();
        }

        $doctor = Doctor::find($id);
        if (!$doctor) {
            $_SESSION['error'] = 'Doctor not found.';
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
            header("Location: {$base}/index.php?r=spital/admin/dashboard");
            exit();
        }

        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => trim($_POST['password'] ?? ''),
            'department' => trim($_POST['department'] ?? ''),
            'specialization' => trim($_POST['specialization'] ?? ''),
            'grade' => trim($_POST['grade'] ?? ''),
        ];

        // Păstrează parola existentă dacă nu se completează
        if ($data['password'] === '') {
            $data['password'] = (string) $doctor['password'];
        } else {
            // Hash pentru parole noi
            $data['password'] = password_hash(
                $data['password'],
                PASSWORD_BCRYPT,
            );
        }

        if (Doctor::update($id, $data)) {
            $_SESSION['msg'] = 'Doctor updated.';
        } else {
            $_SESSION['error'] = 'Update failed.';
        }

        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        header("Location: {$base}/index.php?r=spital/admin/dashboard");
        exit();
    }
    public static function dashboard()
    {
        Auth::requireRole('doctor');
        global $pdo;

        // Pacienți pentru dropdown
        $patients = $pdo
            ->query(
                "
            SELECT u.id, u.first_name, u.last_name, u.email
            FROM users u
            INNER JOIN patients p ON p.user_id = u.id
            ORDER BY u.last_name, u.first_name
        ",
            )
            ->fetchAll(PDO::FETCH_ASSOC);

        // Camere pentru dropdown
        try {
            $rooms = $pdo
                ->query(
                    "
                SELECT r.id, r.room_number, r.capacity
                FROM rooms r
                ORDER BY r.room_number
            ",
                )
                ->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $rooms = [];
        }

        // Ultimele item-uri create (opțional – pentru context)
        $recentRecords = $pdo
            ->query(
                "
            SELECT mr.id, mr.patient_id, u.first_name, u.last_name
            FROM medical_record mr
            JOIN users u ON u.id = mr.patient_id
            ORDER BY mr.id DESC LIMIT 10
        ",
            )
            ->fetchAll(PDO::FETCH_ASSOC);

        $recentConsults = $pdo
            ->query(
                "
            SELECT c.id, c.consultation_date, c.medical_record_id
            FROM consultations c
            ORDER BY c.consultation_date DESC, c.id DESC LIMIT 10
        ",
            )
            ->fetchAll(PDO::FETCH_ASSOC);

        $recentPresc = $pdo
            ->query(
                "
            SELECT p.id, p.patient_id
            FROM prescriptions p
            ORDER BY p.id DESC LIMIT 10
        ",
            )
            ->fetchAll(PDO::FETCH_ASSOC);

        $recentAdmissions = [];
        try {
            $recentAdmissions = $pdo
                ->query(
                    "
                SELECT a.id, a.patient_id, a.admission_date
                FROM admissions a
                ORDER BY a.admission_date DESC, a.id DESC LIMIT 10
            ",
                )
                ->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
        }

        // Medications pentru select (primele 200 alfabetic)
        try {
            $medications = $pdo
                ->query(
                    "
                SELECT id, name
                FROM medications
                WHERE name IS NOT NULL AND name <> ''
                ORDER BY name ASC
                LIMIT 200
            ",
                )
                ->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $medications = [];
        }

        require 'app/views/doctor/dashboard.php';
    }

    public static function createMedicalRecord()
    {
        Auth::requireRole('doctor');
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            !Csrf::verify($_POST['csrf_token'] ?? null)
        ) {
            $_SESSION['error'] = 'Invalid request.';
            return self::back();
        }

        $patientId =
            filter_input(INPUT_POST, 'patient_id', FILTER_VALIDATE_INT) ?: 0;
        $obs = trim($_POST['initial_observations'] ?? '');
        $doctorId = (int) ($_SESSION['user_id'] ?? 0);

        if ($patientId <= 0 || $doctorId <= 0) {
            $_SESSION['error'] = 'Select a patient.';
            return self::back();
        }
        global $pdo;
        $stmt = $pdo->prepare(
            'INSERT INTO medical_record (patient_id, doctor_id, initial_observations) VALUES (:pid, :did, :obs)',
        );
        $ok = $stmt->execute([
            'pid' => $patientId,
            'did' => $doctorId,
            'obs' => $obs,
        ]);
        $_SESSION[$ok ? 'msg' : 'error'] = $ok
            ? 'Medical record created.'
            : 'Failed to create medical record.';
        return self::back();
    }

    public static function createConsultation()
    {
        Auth::requireRole('doctor');
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            !Csrf::verify($_POST['csrf_token'] ?? null)
        ) {
            $_SESSION['error'] = 'Invalid request.';
            return self::back();
        }

        global $pdo;
        $doctorId = self::currentDoctorId();
        $patientId =
            filter_input(INPUT_POST, 'patient_id', FILTER_VALIDATE_INT) ?: 0;
        $patientId = self::normalizePatientId($patientId);
        $mrId =
            filter_input(
                INPUT_POST,
                'medical_record_id',
                FILTER_VALIDATE_INT,
            ) ?:
            0;
        $date = self::normalizeDateTime($_POST['consultation_date'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $diagnosis = trim($_POST['diagnosis'] ?? '');

        if ($patientId <= 0 || $doctorId <= 0 || $date === '') {
            $_SESSION['error'] = 'Patient and date are required.';
            return self::back();
        }

        // Creează MR dacă lipsește
        if ($mrId <= 0) {
            $stmt = $pdo->prepare(
                'INSERT INTO medical_record (patient_id, doctor_id, initial_observations) VALUES (:pid, :did, :obs)',
            );
            $stmt->execute([
                'pid' => $patientId,
                'did' => $doctorId,
                'obs' => 'Auto-created for consultation',
            ]);
            $mrId = (int) $pdo->lastInsertId();
        } else {
            // Verifică MR aparține pacientului
            $chk = $pdo->prepare(
                'SELECT 1 FROM medical_record WHERE id=:id AND patient_id=:pid',
            );
            $chk->execute(['id' => $mrId, 'pid' => $patientId]);
            if (!$chk->fetchColumn()) {
                $_SESSION['error'] =
                    'Medical record does not belong to patient.';
                return self::back();
            }
        }

        // Inserează consultația cu doctors.id
        $stmt = $pdo->prepare("
            INSERT INTO consultations (medical_record_id, doctor_id, consultation_date, notes, diagnosis)
            VALUES (:mr, :did, :dt, :notes, :diag)
        ");
        $ok = $stmt->execute([
            'mr' => $mrId,
            'did' => $doctorId,
            'dt' => $date,
            'notes' => $notes,
            'diag' => $diagnosis,
        ]);
        $_SESSION[$ok ? 'msg' : 'error'] = $ok
            ? 'Consultation saved.'
            : 'Failed to save consultation.';
        return self::back();
    }

    public static function createPrescription()
    {
        Auth::requireRole('doctor');
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            !Csrf::verify($_POST['csrf_token'] ?? null)
        ) {
            $_SESSION['error'] = 'Invalid request.';
            return self::back();
        }

        global $pdo;
        $doctorId = self::currentDoctorId();
        $patientId =
            filter_input(INPUT_POST, 'patient_id', FILTER_VALIDATE_INT) ?: 0;
        $patientId = self::normalizePatientId($patientId);

        // suport multiple medication ids
        $medicationIds = $_POST['medication_ids'] ?? [];
        if (!is_array($medicationIds)) {
            $medicationIds = [];
        }
        $medicationIds = array_values(
            array_unique(
                array_filter(array_map('intval', $medicationIds), function (
                    $v,
                ) {
                    return $v > 0;
                }),
            ),
        );

        $dosage = trim($_POST['dosage'] ?? '');
        $notes = trim($_POST['prescription'] ?? '');

        if ($patientId <= 0 || $doctorId <= 0) {
            $_SESSION['error'] = 'All fields are required.';
            return self::back();
        }

        // citește numele medicamentelor selectate
        $medNames = [];
        if (!empty($medicationIds)) {
            $placeholders = implode(
                ',',
                array_fill(0, count($medicationIds), '?'),
            );
            $stmtMed = $pdo->prepare(
                "SELECT name FROM medications WHERE id IN ($placeholders) ORDER BY name ASC",
            );
            $stmtMed->execute($medicationIds);
            $rows = $stmtMed->fetchAll(PDO::FETCH_COLUMN, 0);
            foreach ($rows as $nm) {
                $nm = trim((string) $nm);
                if ($nm !== '') {
                    $medNames[] = $nm;
                }
            }
        }

        // construiește textul prescripției
        $parts = [];
        if ($medNames) {
            $parts[] = "Medications:\n - " . implode("\n - ", $medNames);
        }
        if ($dosage !== '') {
            $parts[] = "Dosage: {$dosage}";
        }
        if ($notes !== '') {
            $parts[] = $notes;
        }

        $text = trim(implode("\n", $parts));
        if ($text === '') {
            $_SESSION['error'] = 'Prescription text is empty.';
            return self::back();
        }

        $stmt = $pdo->prepare(
            'INSERT INTO prescriptions (patient_id, doctor_id, prescription) VALUES (:pid, :did, :p)',
        );
        $ok = $stmt->execute([
            'pid' => $patientId,
            'did' => $doctorId,
            'p' => $text,
        ]);

        if ($ok) {
            // Trimite prescripția pe email pacientului
            $prescId = (int) $pdo->lastInsertId();

            try {
                $stmtPat = $pdo->prepare("
                    SELECT u.email, u.first_name, u.last_name
                    FROM patients p
                    JOIN users u ON u.id = p.user_id
                    WHERE p.id = :id
                    LIMIT 1
                ");
                $stmtPat->execute(['id' => $patientId]);
                $pat = $stmtPat->fetch(PDO::FETCH_ASSOC);

                if (
                    $pat &&
                    !empty($pat['email']) &&
                    filter_var($pat['email'], FILTER_VALIDATE_EMAIL)
                ) {
                    $to = $pat['email'];
                    $subject = "Prescription #{$prescId}";
                    $body =
                        "Hello {$pat['first_name']} {$pat['last_name']}," .
                        "\n\nHere is your prescription:\n\n{$text}\n\n" .
                        "Best regards,\nHospital";
                    $from =
                        'no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
                    $headers = "From: {$from}\r\n";
                    $headers .= "Reply-To: {$from}\r\n";
                    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

                    if (@mail($to, $subject, $body, $headers)) {
                        $_SESSION['msg'] =
                            'Prescription created and emailed to patient.';
                    } else {
                        $_SESSION['msg'] = 'Prescription created.';
                        $_SESSION['error'] =
                            'Email could not be sent (mail() failed).';
                    }
                } else {
                    $_SESSION['msg'] = 'Prescription created.';
                    $_SESSION['error'] = 'Patient email is missing or invalid.';
                }
            } catch (Throwable $e) {
                $_SESSION['msg'] = 'Prescription created.';
                $_SESSION['error'] = 'Email error: ' . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = 'Failed to create prescription.';
        }

        return self::back();
    }

    public static function createAdmission()
    {
        Auth::requireRole('doctor');
        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST' ||
            !Csrf::verify($_POST['csrf_token'] ?? null)
        ) {
            $_SESSION['error'] = 'Invalid request.';
            return self::back();
        }
        global $pdo;
        $patientId =
            filter_input(INPUT_POST, 'patient_id', FILTER_VALIDATE_INT) ?: 0;
        $patientId = self::normalizePatientId($patientId);
        $roomId = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT) ?: 0;
        $admission = self::normalizeDateTime($_POST['admission_date'] ?? '');
        $discharge = self::normalizeDateTime(
            $_POST['discharge_date'] ?? '',
            allowEmpty: true,
        );

        if ($patientId <= 0 || $roomId <= 0 || $admission === '') {
            $_SESSION['error'] =
                'Patient, room and admission date are required.';
            return self::back();
        }

        $stmt = $pdo->prepare("
            INSERT INTO admissions (patient_id, room_id, admission_date, discharge_date)
            VALUES (:pid, :rid, :ad, :dd)
        ");
        $ok = $stmt->execute([
            'pid' => $patientId,
            'rid' => $roomId,
            'ad' => $admission,
            'dd' => $discharge !== '' ? $discharge : null,
        ]);
        $_SESSION[$ok ? 'msg' : 'error'] = $ok
            ? 'Admission saved.'
            : 'Failed to save admission.';
        return self::back();
    }

    private static function back(): void
    {
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        header("Location: {$base}/index.php?r=spital/doctor/dashboard");
        exit();
    }
    private static function currentDoctorId(): int
    {
        global $pdo;
        $uid = (int) ($_SESSION['user_id'] ?? 0);
        if ($uid <= 0) {
            return 0;
        }
        $stmt = $pdo->prepare(
            'SELECT id FROM doctors WHERE user_id = :uid LIMIT 1',
        );
        $stmt->execute(['uid' => $uid]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    private static function normalizePatientId(int $maybePatientId): int
    {
        global $pdo;
        if ($maybePatientId <= 0) {
            return 0;
        }

        $q1 = $pdo->prepare('SELECT 1 FROM patients WHERE id = :id');
        $q1->execute(['id' => $maybePatientId]);
        if ($q1->fetchColumn()) {
            return $maybePatientId;
        }

        $q2 = $pdo->prepare(
            'SELECT id FROM patients WHERE user_id = :uid LIMIT 1',
        );
        $q2->execute(['uid' => $maybePatientId]);
        return (int) ($q2->fetchColumn() ?: 0);
    }

    private static function normalizeDateTime(
        string $val,
        bool $allowEmpty = false,
    ): string {
        $val = trim($val);
        if ($val === '') {
            return $allowEmpty ? '' : '';
        }
        if (strpos($val, 'T') !== false) {
            $val = str_replace('T', ' ', $val);
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
            $val .= ' 00:00:00';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}$/', $val)) {
            $val .= ':00';
        }
        return $val; // fix: întoarce mereu un string
    }
}
// DE REZOLVAT AZI

// Consulatiosn cu prescriptuoin si last admission probabil au porbleme cu numele de tabel sau au nevoie de anumite campuri adaugate
