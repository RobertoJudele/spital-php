<?php
require_once 'config/pdo.php';

class Patient
{
    public static function getAllPatients(): array
    {
        global $pdo;
        $sql = "
            SELECT u.id AS user_id, u.first_name, u.last_name, u.email,
                   p.id AS patient_id, p.cnp, p.phone, p.address, p.blood_type, p.allergies
            FROM patients p
            JOIN users u ON u.id = p.user_id
            ORDER BY u.last_name, u.first_name
        ";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getPatient(int $userId): ?array
    {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT u.id AS user_id, u.first_name, u.last_name, u.email, u.password,
                   p.id AS patient_id, p.cnp, p.phone, p.address, p.blood_type, p.allergies
            FROM patients p
            JOIN users u ON u.id = p.user_id
            WHERE u.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function create($data)
    {
        global $pdo;
        try {
            if (!$pdo) {
                throw new RuntimeException('PDO not initialized');
            }
            $pdo->beginTransaction();
            $roleId = 2;

            $sqlUser = "INSERT INTO users (first_name, last_name, email, password, role_id, send_notification)
                        VALUES (:first_name, :last_name, :email, :password, :role_id, :send_notification)";
            $stmt = $pdo->prepare($sqlUser);
            $stmt->execute([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => $data['password'], // already hashed
                'role_id' => $roleId,
                'send_notification' => $data['send_notification'] ?? 0,
            ]);
            $userId = (int) $pdo->lastInsertId();

            $sqlPat = "INSERT INTO patients (user_id, cnp, phone, address, blood_type, allergies)
                       VALUES (:user_id, :cnp, :phone, :address, :blood_type, :allergies)";
            $stmt = $pdo->prepare($sqlPat);
            $stmt->execute([
                'user_id' => $userId,
                'cnp' => $data['cnp'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'blood_type' => $data['blood_type'] ?? null,
                'allergies' => $data['allergies'] ?? null,
            ]);

            $pdo->commit();
            return $userId;
        } catch (Throwable $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Patient create failed: ' . $e->getMessage());
            return false;
        }
    }

    private static function getPacientRoleId(): int
    {
        global $pdo;
        $stmt = $pdo->prepare(
            "SELECT id FROM user_roles WHERE name = 'pacient' LIMIT 1",
        );
        $stmt->execute();
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : 1;
    }

    public static function update(int $userId, array $data): bool
    {
        global $pdo;
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                UPDATE users
                   SET first_name = :first_name,
                       last_name  = :last_name,
                       email      = :email,
                       password   = :password
                 WHERE id = :id
            ");
            $stmt->execute([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'id' => $userId,
            ]);

            $stmt = $pdo->prepare("
                UPDATE patients
                   SET cnp        = :cnp,
                       phone      = :phone,
                       address    = :address,
                       blood_type = :blood_type,
                       allergies  = :allergies
                 WHERE user_id = :id
            ");
            $stmt->execute([
                'cnp' => $data['cnp'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'blood_type' => $data['blood_type'],
                'allergies' => $data['allergies'],
                'id' => $userId,
            ]);

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Patient update failed: ' . $e->getMessage());
            return false;
        }
    }

    public static function delete($userId)
    {
        global $pdo;
        // delete the user; patient row will be deleted via ON DELETE CASCADE
        $sql = 'DELETE FROM users WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(['id' => $userId]);
    }
}
?>
