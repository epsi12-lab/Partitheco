<?php
// classes/LoginThrottle.php - Limitation des tentatives de connexion (anti brute-force)

declare(strict_types=1);

namespace App;

use PDO;

class LoginThrottle {
    private PDO $pdo;
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->initTable();
    }

    private function initTable(): void {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS login_attempts (
                id SERIAL PRIMARY KEY,
                identifier TEXT NOT NULL,
                ip_address TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public function isLocked(string $identifier, string $ip): bool {
        return $this->countRecentFailures($identifier, $ip) >= self::MAX_ATTEMPTS;
    }

    public function recordFailure(string $identifier, string $ip): void {
        $stmt = $this->pdo->prepare("INSERT INTO login_attempts (identifier, ip_address) VALUES (:id, :ip)");
        $stmt->execute([':id' => $identifier, ':ip' => $ip]);
    }

    public function clear(string $identifier, string $ip): void {
        $stmt = $this->pdo->prepare("DELETE FROM login_attempts WHERE identifier = :id AND ip_address = :ip");
        $stmt->execute([':id' => $identifier, ':ip' => $ip]);
    }

    private function countRecentFailures(string $identifier, string $ip): int {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM login_attempts
            WHERE identifier = :id AND ip_address = :ip
              AND created_at > NOW() - INTERVAL '" . self::LOCKOUT_MINUTES . " minutes'
        ");
        $stmt->execute([':id' => $identifier, ':ip' => $ip]);
        return (int) $stmt->fetchColumn();
    }
}
