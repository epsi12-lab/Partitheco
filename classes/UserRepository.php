<?php
// classes/UserRepository.php

declare(strict_types=1);

namespace App;

use PDO;

class UserRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function register(
        string $username,
        string $email,
        string $hashedPassword,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $paroisse = null,
        ?string $roleChoral = null
    ): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, email, password, first_name, last_name, paroisse, role_choral)
            VALUES (:username, :email, :password, :first_name, :last_name, :paroisse, :role_choral)
        ");

        return $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':paroisse' => $paroisse,
            ':role_choral' => $roleChoral,
        ]);
    }

    public function findForLogin(string $username): array|false {
        $stmt = $this->pdo->prepare("
            SELECT id, username, email, password, first_name, last_name, paroisse, role_choral
            FROM users
            WHERE username = :username
            LIMIT 1
        ");
        $stmt->execute([':username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByUsername(string $username): array|false {
        $stmt = $this->pdo->prepare("
            SELECT id, username, email, first_name, last_name, paroisse, role_choral
            FROM users
            WHERE username = :username
            LIMIT 1
        ");
        $stmt->execute([':username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById(int $userId): array|false {
        $stmt = $this->pdo->prepare("
            SELECT id, username, email, first_name, last_name, paroisse, role_choral
            FROM users
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile(int $userId, ?string $paroisse, ?string $roleChoral): bool {
        $stmt = $this->pdo->prepare("
            UPDATE users
            SET paroisse = :paroisse, role_choral = :role
            WHERE id = :id
        ");

        return $stmt->execute([
            ':paroisse' => $paroisse,
            ':role' => $roleChoral,
            ':id' => $userId,
        ]);
    }

    public function createRememberToken(int $userId, string $tokenHash, string $expiresAt): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO remember_tokens (user_id, token_hash, expires_at)
            VALUES (:uid, :token_hash, :expires_at)
        ");

        return $stmt->execute([
            ':uid' => $userId,
            ':token_hash' => $tokenHash,
            ':expires_at' => $expiresAt,
        ]);
    }

    public function findByRememberTokenHash(string $tokenHash): array|false {
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.username, u.email, u.first_name, u.last_name, u.paroisse, u.role_choral
            FROM remember_tokens rt
            JOIN users u ON u.id = rt.user_id
            WHERE rt.token_hash = :token_hash
              AND rt.expires_at > CURRENT_TIMESTAMP
            LIMIT 1
        ");
        $stmt->execute([':token_hash' => $tokenHash]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteRememberTokenHash(string $tokenHash): bool {
        $stmt = $this->pdo->prepare("
            DELETE FROM remember_tokens
            WHERE token_hash = :token_hash
        ");
        return $stmt->execute([':token_hash' => $tokenHash]);
    }

    public function clearExpiredRememberTokens(): void {
        $this->pdo->exec("DELETE FROM remember_tokens WHERE expires_at <= CURRENT_TIMESTAMP");
    }

    public function initRememberTokensTable(): void {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS remember_tokens (
                id SERIAL PRIMARY KEY,
                user_id INTEGER NOT NULL REFERENCES users(id),
                token_hash TEXT NOT NULL UNIQUE,
                expires_at TIMESTAMP NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }
}
