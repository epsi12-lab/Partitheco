<?php

namespace App;

use PDO;

class Favorite {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function add(int $userId, int $projectId): bool {
        $stmt = $this->pdo->prepare("INSERT OR IGNORE INTO favorites (user_id, project_id) VALUES (:uid, :pid)");
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':pid', $projectId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function remove(int $userId, int $projectId): bool {
        $stmt = $this->pdo->prepare("DELETE FROM favorites WHERE user_id = :uid AND project_id = :pid");
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':pid', $projectId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function isFavorite(int $userId, int $projectId): bool {
        $stmt = $this->pdo->prepare("SELECT 1 FROM favorites WHERE user_id = :uid AND project_id = :pid");
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':pid', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }

    public function getByUser(int $userId): array {
        $stmt = $this->pdo->prepare("
            SELECT p.*, u.username as publisher
            FROM projects p
            JOIN favorites f ON p.id = f.project_id
            JOIN users u ON p.user_id = u.id
            WHERE f.user_id = :uid
            ORDER BY f.created_at DESC
        ");
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
