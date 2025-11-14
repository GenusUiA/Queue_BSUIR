<?php

class QueueRepository
{
    private PDO $db;
//
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // === Add (аналог твоего Add(int userId, int dateId, int numberUser)) ===
    public function add(int $userId, int $dateId, int $numberUser): bool
    {
        if ($userId <= 0 || $dateId <= 0 || $numberUser <= 0) {
            return false;
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO queue (UserId, DateId, NumberUser)
                VALUES (?, ?, ?)
            ");

            $stmt->execute([$userId, $dateId, $numberUser]);

            return true;
        } catch (Exception $e) {

            error_log("Error occurred while adding to queue: " . $e->getMessage());

            return false;
        }
    }

    // === Delete (аналог Delete(int queueId)) ===
    public function delete(int $queueId): bool
    {
        // Проверяем существование
        $stmt = $this->db->prepare("SELECT * FROM queue WHERE Id = ?");
        $stmt->execute([$queueId]);

        if (!$stmt->fetch(PDO::FETCH_OBJ)) {
            return false;
        }

        // Удаляем
        $stmt = $this->db->prepare("DELETE FROM queue WHERE Id = ?");
        return $stmt->execute([$queueId]);
    }

    // === GetUser (полный аналог как в C#) ===
    public function getUser(string $password, string $email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE Email = ?");
        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$user) return null;

        // Проверка как PasswordHasher.VerifyHashedPassword
        if (password_verify($password, $user->Password)) {
            return $user;
        }

        return null;
    }
}
