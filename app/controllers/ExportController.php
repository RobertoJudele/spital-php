<?php
require_once 'app/middleware/Auth.php';

class ExportController
{
    // Export pacienți în CSV/Excel
    public static function exportPatients()
    {
        Auth::requireRole('admin');
        global $pdo;

        $patients = $pdo
            ->query(
                "
            SELECT u.first_name, u.last_name, u.email, p.cnp, p.phone, p.blood_type, p.allergies
            FROM patients p
            JOIN users u ON u.id = p.user_id
            ORDER BY u.last_name
        ",
            )
            ->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv; charset=utf-8');
        header(
            'Content-Disposition: attachment; filename=patients_export_' .
                date('Y-m-d') .
                '.csv',
        );

        $output = fopen('php://output', 'w');
        fputcsv($output, [
            'First Name',
            'Last Name',
            'Email',
            'CNP',
            'Phone',
            'Blood Type',
            'Allergies',
        ]);

        foreach ($patients as $patient) {
            fputcsv($output, $patient);
        }

        fclose($output);
    }

    // Export raport medical în PDF
    public static function exportMedicalReport($patientId)
    {
        Auth::requireRole(['doctor', 'admin']);
        global $pdo;

        // Generează PDF cu TCPDF sau DomPDF
        require_once 'vendor/autoload.php'; // Composer autoload

        $pdf = new TCPDF(
            PDF_PAGE_ORIENTATION,
            PDF_UNIT,
            PDF_PAGE_FORMAT,
            true,
            'UTF-8',
            false,
        );
        $pdf->SetCreator('Hospital System');
        $pdf->SetTitle('Medical Report');

        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);

        $patient = $pdo->prepare("
            SELECT u.first_name, u.last_name, u.email, p.cnp, p.blood_type
            FROM patients p
            JOIN users u ON u.id = p.user_id
            WHERE p.id = :id
        ");
        $patient->execute(['id' => $patientId]);
        $patientData = $patient->fetch(PDO::FETCH_ASSOC);

        $html = "
        <h1>Medical Report</h1>
        <h2>Patient Information</h2>
        <p><strong>Name:</strong> {$patientData['first_name']} {$patientData['last_name']}</p>
        <p><strong>Email:</strong> {$patientData['email']}</p>
        <p><strong>CNP:</strong> {$patientData['cnp']}</p>
        <p><strong>Blood Type:</strong> {$patientData['blood_type']}</p>
        ";

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('medical_report_' . $patientId . '.pdf', 'D');
    }

    // Import pacienți din CSV
    public static function importPatients()
    {
        if (
            $_SERVER['REQUEST_METHOD'] === 'POST' &&
            isset($_FILES['csv_file'])
        ) {
            $file = $_FILES['csv_file']['tmp_name'];
            $handle = fopen($file, 'r');

            // Skip header row
            fgetcsv($handle);

            global $pdo;
            $imported = 0;

            while (($data = fgetcsv($handle)) !== false) {
                try {
                    $pdo->beginTransaction();

                    // Insert user
                    $stmt = $pdo->prepare(
                        'INSERT INTO users (first_name, last_name, email, password, role_id) VALUES (?, ?, ?, ?, 2)',
                    );
                    $stmt->execute([
                        $data[0],
                        $data[1],
                        $data[2],
                        password_hash('default123', PASSWORD_BCRYPT),
                    ]);
                    $userId = $pdo->lastInsertId();

                    // Insert patient
                    $stmt = $pdo->prepare(
                        'INSERT INTO patients (user_id, cnp, phone, blood_type) VALUES (?, ?, ?, ?)',
                    );
                    $stmt->execute([$userId, $data[3], $data[4], $data[5]]);

                    $pdo->commit();
                    $imported++;
                } catch (Exception $e) {
                    $pdo->rollback();
                }
            }

            fclose($handle);
            $_SESSION['msg'] = "Imported {$imported} patients successfully.";
        }

        require_once 'app/views/admin/import_patients.php';
    }
}
