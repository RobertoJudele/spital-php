<?php
require_once 'app/middleware/Auth.php';
class MedicalRecordController
{
    public static function show()
    {
        Auth::requireRole('pacient');
        global $pdo;
        if (!$pdo) {
            throw new RuntimeException('PDO not initialized');
        }
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
        $uid = (int) ($_SESSION['user_id'] ?? 0);
        $sql = "SELECT mr.*, du.first_name AS doctor_first, du.last_name AS doctor_last
            FROM medical_record mr
            LEFT JOIN users du ON du.id = mr.doctor_id
            WHERE mr.id = :id AND mr.patient_id = :uid";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id, 'uid' => $uid]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$record) {
            http_response_code(404);
            require 'app/views/404.php';
            return;
        }
        require 'app/views/medical_records/show.php';
    }
}
