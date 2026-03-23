<?php
// classes/AuthService.php

namespace App;

use PDO;

class AuthService {
    private User $user;

    public function __construct(PDO $pdo) {
        $this->user = new User($pdo);
    }

    public function login(string $username, string $password): array|false {
        return $this->user->login($username, $password);
    }

    public function loginFromRememberToken(string $token): array|false {
        return $this->user->findByRememberToken($token);
    }

    public function issueRememberToken(int $userId): string {
        return $this->user->createRememberToken($userId);
    }

    public function revokeRememberToken(string $token): bool {
        return $this->user->deleteRememberToken($token);
    }

    public function startAuthenticatedSession(array $user): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
    }
}
