<?php
require_once 'app/middleware/Auth.php';

class ExportController
{
    // Pacientul logat își exportă propriile prescripții
    public static function exportMyPrescriptionsCsv()
    {
        Auth::requireRole('pacient');
        global $pdo;

        $uid = (int) ($_SESSION['user_id'] ?? 0);
        if ($uid <= 0) {
            http_response_code(403);
            exit('Not authenticated');
        }

        // Găsește patient_id pentru userul curent
        $stmt = $pdo->prepare(
            'SELECT id FROM patients WHERE user_id = :uid LIMIT 1',
        );
        $stmt->execute(['uid' => $uid]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            http_response_code(404);
            exit('Patient not found');
        }
        $patientId = (int) $row['id'];

        // Export CSV
        header('Content-Type: text/csv; charset=utf-8');
        header(
            'Content-Disposition: attachment; filename=prescriptions_' .
                $patientId .
                '_' .
                date('Y-m-d') .
                '.csv',
        );

        $out = fopen('php://output', 'w');
        fputcsv($out, ['#', 'Doctor', 'Prescription']);

        $q = "
            SELECT p.id, p.prescription,
                   du.first_name AS doctor_first, du.last_name AS doctor_last
            FROM prescriptions p
            JOIN doctors d ON d.id = p.doctor_id
            JOIN users du   ON du.id = d.user_id
            WHERE p.patient_id = :pid
            ORDER BY p.id ASC
        ";
        $stmt = $pdo->prepare($q);
        $stmt->execute(['pid' => $patientId]);

        $i = 0;
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $i++;
            $doctor = trim(
                ($r['doctor_last'] ?? '') . ' ' . ($r['doctor_first'] ?? ''),
            );
            fputcsv($out, [$i, $doctor, (string) ($r['prescription'] ?? '')]);
        }
        fclose($out);
        exit();
    }

    // Admin/Doctor: exportă prescripțiile pentru un pacient dat
    public static function exportPatientPrescriptionsCsv($patientId)
    {
        Auth::requireRole(['admin', 'doctor']);
        global $pdo;

        $pid = (int) $patientId;
        if ($pid <= 0) {
            http_response_code(400);
            exit('Invalid patient id');
        }

        header('Content-Type: text/csv; charset=utf-8');
        header(
            'Content-Disposition: attachment; filename=prescriptions_' .
                $pid .
                '_' .
                date('Y-m-d') .
                '.csv',
        );

        $out = fopen('php://output', 'w');
        fputcsv($out, ['#', 'Doctor', 'Prescription']);

        $q = "
            SELECT p.id, p.prescription,
                   du.first_name AS doctor_first, du.last_name AS doctor_last
            FROM prescriptions p
            JOIN doctors d ON d.id = p.doctor_id
            JOIN users du   ON du.id = d.user_id
            WHERE p.patient_id = :pid
            ORDER BY p.id ASC
        ";
        $stmt = $pdo->prepare($q);
        $stmt->execute(['pid' => $pid]);

        $i = 0;
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $i++;
            $doctor = trim(
                ($r['doctor_last'] ?? '') . ' ' . ($r['doctor_first'] ?? ''),
            );
            fputcsv($out, [$i, $doctor, (string) ($r['prescription'] ?? '')]);
        }
        fclose($out);
        exit();
    }
}
