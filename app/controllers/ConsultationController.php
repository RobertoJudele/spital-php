<?php
require_once 'app/middleware/Auth.php';
class ConsultationController
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
        $sql = "SELECT c.*, du.first_name AS doctor_first, du.last_name AS doctor_last
            FROM consultations c
            JOIN medical_record mr ON mr.id = c.medical_record_id
            LEFT JOIN users du ON du.id = c.doctor_id
            WHERE c.id = :id AND mr.patient_id = :uid";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id, 'uid' => $uid]);
        $consultation = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$consultation) {
            http_response_code(404);
            require 'app/views/404.php';
            return;
        }
        require 'app/views/consultations/show.php';
    }
}
