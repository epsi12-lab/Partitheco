<?php
// classes/User.php

declare(strict_types=1);

namespace App;

use PDO;

class User {
    private UserRepository $repository;

    public function __construct(PDO $pdo) {
        $this->repository = new UserRepository($pdo);
        $this->repository->initRememberTokensTable();
    }

    public function register(string $username, string $email, string $password, ?string $firstName = null, ?string $lastName = null, ?string $paroisse = null, ?string $roleChoral = null): bool {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        return $this->repository->register(
            $username,
            $email,
            $hashedPassword,
            $firstName,
            $lastName,
            $paroisse,
            $roleChoral
        );
    }

    public function login(string $username, string $password) {
        $user = $this->repository->findForLogin($username);

        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }
        return false;
    }

    public function findByUsername(string $username) {
        return $this->repository->findByUsername($username);
    }

    public function updateProfile(int $userId, ?string $paroisse, ?string $role_choral): bool {
        return $this->repository->updateProfile($userId, $paroisse, $role_choral);
    }

    public function createRememberToken(int $userId): string {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = (new \DateTimeImmutable('+30 days'))->format('Y-m-d H:i:s');

        $this->repository->createRememberToken($userId, $tokenHash, $expiresAt);

        return $token;
    }

    public function findByRememberToken(string $token) {
        $this->repository->clearExpiredRememberTokens();
        return $this->repository->findByRememberTokenHash(hash('sha256', $token));
    }

    public function deleteRememberToken(string $token): bool {
        return $this->repository->deleteRememberTokenHash(hash('sha256', $token));
    }
}
