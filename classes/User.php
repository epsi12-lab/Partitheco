<?php
// classes/User.php

namespace App;

use PDO;

class User {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function register(string $username, string $email, string $password): bool {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function login(string $username, string $password) {
        $stmt = $this->pdo->prepare("
            SELECT id, username, email, password, paroisse, role_choral
            FROM users
            WHERE username = :username
            LIMIT 1
        ");
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }
        return false;
    }

    public function findByUsername(string $username) {
        $stmt = $this->pdo->prepare("
            SELECT id, username, email, paroisse, role_choral
            FROM users
            WHERE username = :username
            LIMIT 1
        ");
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile(int $userId, ?string $paroisse, ?string $role_choral): bool {
        $stmt = $this->pdo->prepare("
            UPDATE users
            SET paroisse = :paroisse, role_choral = :role
            WHERE id = :id
        ");
        $stmt->bindValue(':paroisse', $paroisse, PDO::PARAM_STR);
        $stmt->bindValue(':role',     $role_choral, PDO::PARAM_STR);
        $stmt->bindValue(':id',       $userId,      PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
