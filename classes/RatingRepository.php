<?php
// classes/RatingRepository.php

declare(strict_types=1);

namespace App;

use PDO;

class RatingRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function rate(int $userId, int $projectId, int $score): bool {
        if ($score < 1 || $score > 5) {
            return false;
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO ratings (user_id, project_id, score)
            VALUES (:uid, :pid, :score)
            ON CONFLICT(user_id, project_id)
            DO UPDATE SET score = :score2, updated_at = CURRENT_TIMESTAMP
        ");

        return $stmt->execute([
            ':uid' => $userId,
            ':pid' => $projectId,
            ':score' => $score,
            ':score2' => $score,
        ]);
    }

    public function getUserRating(int $userId, int $projectId): ?int {
        $stmt = $this->pdo->prepare("
            SELECT score
            FROM ratings
            WHERE user_id = :uid AND project_id = :pid
        ");
        $stmt->execute([
            ':uid' => $userId,
            ':pid' => $projectId,
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int) $result['score'] : null;
    }

    public function getAverageRating(int $projectId): ?float {
        $stmt = $this->pdo->prepare("
            SELECT AVG(score) AS avg, COUNT(*) AS count
            FROM ratings
            WHERE project_id = :pid
        ");
        $stmt->execute([':pid' => $projectId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result && $result['count'] > 0 ? round((float) $result['avg'], 1) : null;
    }

    public function getRatingCount(int $projectId): int {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM ratings
            WHERE project_id = :pid
        ");
        $stmt->execute([':pid' => $projectId]);
        return (int) $stmt->fetchColumn();
    }
}
