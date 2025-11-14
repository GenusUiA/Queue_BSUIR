<?php

class UserRepository
{
    private PDO $dbContext;

    // статические переменные, как в C#
    private static string $secretKey = "super_super_secret_key_with_256_bits_!!!";
    private static string $issuer = "MyApp";
    private static string $audience = "MyUsers";

    public function __construct(PDO $dbContext)
    {
        $this->dbContext = $dbContext;
    }
//
    public function Add(string $username, string $password, string $email, int $groupId, int $subgroupNumber)
    {
        // аналог PasswordHasher<User>
        // проверка уникальности username
        $stmt = $this->dbContext->prepare("SELECT username FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $existing = $stmt->fetchColumn();

        if ($existing !== false) {
            return null;
        }

        // HashPassword
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // создаём user объект как массив (или можно сделать класс User)
        $user = [
            "UserName" => $username,
            "Password" => $hashedPassword,
            "Email" => $email,
            "GroupId" => $groupId,
            "SubgroupNumber" => $subgroupNumber
        ];

        // AddAsync
        $stmt = $this->dbContext->prepare("
            INSERT INTO users (username, password, email, group_id, subgroup_number)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user["UserName"],
            $user["Password"],
            $user["Email"],
            $user["GroupId"],
            $user["SubgroupNumber"]
        ]);

        // SaveChangesAsync — эквивалентно PDO вставке
        $user["Id"] = $this->dbContext->lastInsertId();

        return $user;
    }

    public function GetUser(string $Password, string $Email)
    {
        // аналог FirstOrDefaultAsync
        $stmt = $this->dbContext->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$Email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return null;
        }

        // VerifyHashedPassword
        $verification = password_verify($Password, $user["password"]);

        return $verification ? $user : null;
    }
}
