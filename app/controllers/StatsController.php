<?php
require_once 'app/middleware/Auth.php';

class StatsController
{
    public static function dashboard()
    {
        Auth::requireRole('admin');
        require_once 'app/views/stats/dashboard.php';
    }

    public static function data()
    {
        Auth::requireRole('admin');
        global $pdo;

        $stats = [];

        try {
            $stats['appointments_by_status'] = $pdo
                ->query(
                    "
                SELECT 
                    CASE 
                        WHEN status IS NULL THEN 'Pending'
                        WHEN status = 1 THEN 'Approved' 
                        ELSE 'Rejected' 
                    END as status_name,
                    COUNT(*) as count
                FROM appointments 
                GROUP BY status
            ",
                )
                ->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $stats['appointments_by_status'] = [
                ['status_name' => 'Pending', 'count' => 0],
                ['status_name' => 'Approved', 'count' => 0],
                ['status_name' => 'Rejected', 'count' => 0],
            ];
        }

        try {
            $stats['patients_by_blood_type'] = $pdo
                ->query(
                    "
                SELECT COALESCE(blood_type, 'Unknown') as blood_type, COUNT(*) as count
                FROM patients
                GROUP BY blood_type
            ",
                )
                ->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $stats['patients_by_blood_type'] = [];
        }

        try {
            $stats['doctors_by_department'] = $pdo
                ->query(
                    "
                SELECT COALESCE(department, 'General') as department, COUNT(*) as count
                FROM doctors
                GROUP BY department
            ",
                )
                ->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $stats['doctors_by_department'] = [];
        }

        try {
            $occupied =
                (int) ($pdo
                    ->query(
                        "
                SELECT COUNT(*) FROM admissions WHERE discharge_date IS NULL
            ",
                    )
                    ->fetchColumn() ?:
                0);
            $totalRooms =
                (int) ($pdo
                    ->query('SELECT COUNT(*) FROM rooms')
                    ->fetchColumn() ?:
                0);
            $stats['room_occupancy'] = [
                ['status' => 'Occupied', 'count' => $occupied],
                [
                    'status' => 'Available',
                    'count' => max(0, $totalRooms - $occupied),
                ],
            ];
        } catch (Throwable $e) {
            $stats['room_occupancy'] = [];
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($stats, JSON_UNESCAPED_UNICODE);
        exit();
    }
}
