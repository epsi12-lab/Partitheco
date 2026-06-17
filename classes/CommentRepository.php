<?php
// classes/CommentRepository.php

declare(strict_types=1);

namespace App;

use PDO;

class CommentRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getByProject(int $projectId): array {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM comments
            WHERE project_id = :pid
            ORDER BY created_at DESC
        ");
        $stmt->execute([':pid' => $projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert(int $projectId, string $author, string $content): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO comments (project_id, author, content)
            VALUES (:pid, :author, :content)
        ");
        return $stmt->execute([
            ':pid' => $projectId,
            ':author' => $author,
            ':content' => $content,
        ]);
    }
}
