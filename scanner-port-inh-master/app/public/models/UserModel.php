<?php

require_once(__DIR__ . "/BaseModel.php");
require_once(__DIR__ . "/../dto/UserDTO.php");

class UserModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }


    public function getAll(): array
    {
        return [
            new UserDTO(1, "foo@foo.com", "foo_user"),
            new UserDTO(2, "bar@bar.com", "bar_user"),
            new UserDTO(3, "baz@baz.com", "baz_user")
        ];
    }

    public function get(int $id): UserDTO
    {
        return new UserDTO(1, "foo@foo.com", "foo_user");
    }

    public function createUser($username, $plainPassword, $email)
    {
        // Hash the password
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        // Insert user into DB
        $sql = "INSERT INTO users (username, password, email, role) 
                VALUES (:username, :password, :email, 'user')";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([
            ':username' => $username,
            ':password' => $hashedPassword,
            ':email'    => $email
        ]);

        return self::$pdo->lastInsertId();
    }
    public function getByUsernameOrEmail($value)
    {
        // Could check if $value looks like an email or always check both
        // Here, let's do a single query that checks both columns
        $sql = "SELECT * FROM users 
                WHERE username = :value OR email = :value 
                LIMIT 1";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([':value' => $value]);
        return $stmt->fetch(); // returns false if no row found, or an assoc array if found
    }
    public function getAllUsers() {
        $sql = "SELECT * FROM users";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(); // false if not found
    }

    public function createPasswordReset($email, $token)
    {
        // Insert new token for this email
        $sql = "INSERT INTO password_resets (email, token) 
            VALUES (:email, :token)";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':token' => $token
        ]);
    }

    public function findPasswordReset($token)
    {
        $sql = "SELECT * FROM password_resets WHERE token = :token LIMIT 1";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([':token' => $token]);
        return $stmt->fetch(); // false if none
    }

    public function deletePasswordReset($token)
    {
        $sql = "DELETE FROM password_resets WHERE token = :token";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([':token' => $token]);
    }

    public function updateUserPasswordByEmail($email, $hashedPassword)
    {
        $sql = "UPDATE users SET password = :pwd WHERE email = :email";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([
            ':pwd' => $hashedPassword,
            ':email' => $email
        ]);
    }
}
