<?php
// classes/Rating.php - Système de notation des partitions

namespace App;

use PDO;

class Rating {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function rate(int $userId, int $projectId, int $score): bool {
        if ($score < 1 || $score > 5) return false;
        
        $stmt = $this->pdo->prepare("
            INSERT INTO ratings (user_id, project_id, score)
            VALUES (:uid, :pid, :score)
            ON CONFLICT(user_id, project_id) DO UPDATE SET score = :score2, updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':pid', $projectId, PDO::PARAM_INT);
        $stmt->bindValue(':score', $score, PDO::PARAM_INT);
        $stmt->bindValue(':score2', $score, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getUserRating(int $userId, int $projectId): ?int {
        $stmt = $this->pdo->prepare("
            SELECT score FROM ratings WHERE user_id = :uid AND project_id = :pid
        ");
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':pid', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['score'] : null;
    }

    public function getAverageRating(int $projectId): ?float {
        $stmt = $this->pdo->prepare("
            SELECT AVG(score) as avg, COUNT(*) as count FROM ratings WHERE project_id = :pid
        ");
        $stmt->bindValue(':pid', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result && $result['count'] > 0 ? round((float)$result['avg'], 1) : null;
    }

    public function getRatingCount(int $projectId): int {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM ratings WHERE project_id = :pid");
        $stmt->bindValue(':pid', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
}
