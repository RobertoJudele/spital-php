<?php
require_once 'app/middleware/Auth.php';
class PrescriptionController
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
        $sql = "SELECT p.*, du.first_name AS doctor_first, du.last_name AS doctor_last
            FROM prescriptions p
            LEFT JOIN users du ON du.id = p.doctor_id
            WHERE p.id = :id AND p.patient_id = :uid";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id, 'uid' => $uid]);
        $prescription = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$prescription) {
            http_response_code(404);
            require 'app/views/404.php';
            return;
        }
        require 'app/views/prescriptions/show.php';
    }
}
