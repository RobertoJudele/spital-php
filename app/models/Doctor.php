<?php
require_once 'config/pdo.php';
class Doctor
{
    public static function getAllDoctors()
    {
        global $pdo;

        $sql =
            'SELECT u.id as user_id,u.first_name,u.last_name,u.email,d.specialization,d.department,d.grade FROM Users u join Doctors d on d.user_id=u.id';
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getDoctor($id)
    {
        global $pdo;
        $sql =
            'SELECT u.id as user_id,u.first_name,u.last_name,u.email,d.specialization,d.department,d.grade FROM Users u join Doctors d on d.user_id=u.id where u.id=:id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($data)
    {
        global $pdo;
        try {
            if (!$pdo) {
                throw new RuntimeException('PDO not initialized');
            }
            $pdo->beginTransaction();
            $roleId = 4;
            $sql = "INSERT INTO users (first_name, last_name, email, password, role_id, send_notification)
                        VALUES (:first_name, :last_name, :email, :password, :role_id, :send_notification)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => $data['password'], // already hashed
                'role_id' => $roleId,
                'send_notification' => $data['send_notification'] ?? 0,
            ]);
            $userId = (int) $pdo->lastInsertId();
            $sqlDoc =
                'INSERT INTO doctors(user_id ,specialization ,department ,grade ) VALUES(:user_id,:specialization,:department,:grade)';
            $stmt = $pdo->prepare($sqlDoc);
            $stmt->execute([
                'user_id' => $userId,
                'specialization' => $data['specialization'],
                'department' => $data['department'],
                'grade' => $data['grade'],
            ]);
            $pdo->commit();
            return $userId;
        } catch (Throwable $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Doctor create failed: ' . $e->getMessage());
            return false;
        }
        if (!$pdo) {
        }
    }
    public static function update(int $userId, array $data)
    {
        global $pdo;
        try {
            if (!$pdo) {
                throw new RuntimeException('PDO not initialized');
            }
            if (!self::exists($userId)) {
                error_log("Doctor with user_id {$userId} does not exist.");
                return false;
            }
            $sqlUser = "UPDATE users
                        SET first_name=:first_name, last_name=:last_name, email=:email, password=:password
                        WHERE id=:id";
            $stmt = $pdo->prepare($sqlUser);
            $stmt->execute([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'id' => $userId,
            ]);
            $sqlDoc =
                'UPDATE doctors SET specialization=:specialization,department=:department,grade=:grade where user_id=:id';
            $stmt = $pdo->prepare($sqlDoc);
            $stmt->execute([
                'id' => $userId,
                'specialization' => $data['specialization'],
                'department' => $data['department'],
                'grade' => $data['grade'],
            ]);
            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Doctor update failed: ' . $e->getMessage());
            return false;
        }
    }
    public static function delete(int $userId)
    {
        global $pdo;
        try {
            if (!$pdo) {
                throw new RuntimeException('PDO not initialized');
            }
            if (!self::exists($userId)) {
                error_log("Doctor with user_id {$userId} does not exist.");
                return false;
            }
            $sql = 'DELETE FROM users where id=:id';
            $stmt = $pdo->prepare($sql);
            return $stmt->execute(['id' => $userId]);
        } catch (Throwable $e) {
        }
    }
    public static function exists(int $userId): bool
    {
        global $pdo;

        $sql = 'SELECT COUNT(*) FROM doctors WHERE user_id = :user_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchColumn() > 0;
    }
    public static function find(int $userId): ?array
    {
        global $pdo;
        $sql = "SELECT u.id, u.first_name, u.last_name, u.email, u.password,
                   d.department, d.specialization, d.grade
            FROM users u
            INNER JOIN doctors d ON d.user_id = u.id
            WHERE u.id = :id
            LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
