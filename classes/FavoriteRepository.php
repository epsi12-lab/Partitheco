<?php
// classes/FavoriteRepository.php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

class FavoriteRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function add(int $userId, int $projectId): bool {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO favorites (user_id, project_id)
                VALUES (:uid, :pid)
            ");
            return $stmt->execute([
                ':uid' => $userId,
                ':pid' => $projectId,
            ]);
        } catch (PDOException $e) {
            if (in_array($e->getCode(), ['23000', '23505'], true)) {
                return true;
            }
            throw $e;
        }
    }

    public function remove(int $userId, int $projectId): bool {
        $stmt = $this->pdo->prepare("
            DELETE FROM favorites
            WHERE user_id = :uid AND project_id = :pid
        ");
        return $stmt->execute([
            ':uid' => $userId,
            ':pid' => $projectId,
        ]);
    }

    public function isFavorite(int $userId, int $projectId): bool {
        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM favorites
            WHERE user_id = :uid AND project_id = :pid
        ");
        $stmt->execute([
            ':uid' => $userId,
            ':pid' => $projectId,
        ]);
        return (bool) $stmt->fetchColumn();
    }

    public function getByUser(int $userId): array {
        $stmt = $this->pdo->prepare("
            SELECT p.*, u.username AS publisher
            FROM projects p
            JOIN favorites f ON p.id = f.project_id
            JOIN users u ON p.user_id = u.id
            WHERE f.user_id = :uid
            ORDER BY f.created_at DESC
        ");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
