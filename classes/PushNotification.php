<?php
// classes/PushNotification.php - Gestion des notifications push

namespace App;

use PDO;

class PushNotification {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->initTable();
    }

    private function initTable(): void {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS push_subscriptions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                endpoint TEXT NOT NULL UNIQUE,
                p256dh TEXT NOT NULL,
                auth TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY(user_id) REFERENCES users(id)
            )
        ");
    }

    public function subscribe(?int $userId, string $endpoint, string $p256dh, string $auth): bool {
        $stmt = $this->pdo->prepare("
            INSERT OR REPLACE INTO push_subscriptions (user_id, endpoint, p256dh, auth)
            VALUES (:uid, :endpoint, :p256dh, :auth)
        ");
        return $stmt->execute([
            ':uid' => $userId,
            ':endpoint' => $endpoint,
            ':p256dh' => $p256dh,
            ':auth' => $auth
        ]);
    }

    public function unsubscribe(string $endpoint): bool {
        $stmt = $this->pdo->prepare("DELETE FROM push_subscriptions WHERE endpoint = :endpoint");
        return $stmt->execute([':endpoint' => $endpoint]);
    }

    public function getAllSubscriptions(): array {
        $stmt = $this->pdo->query("SELECT * FROM push_subscriptions");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUser(int $userId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM push_subscriptions WHERE user_id = :uid");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
