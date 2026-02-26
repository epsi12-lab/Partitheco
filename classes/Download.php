<?php
// classes/Download.php

namespace App;

use PDO;

class Download {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function record(int $projectId, ?int $userId, string $ipAddress, string $fileType): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO downloads (project_id, user_id, ip_address, file_type)
            VALUES (:pid, :uid, :ip, :type)
        ");
        $stmt->bindValue(':pid', $projectId, PDO::PARAM_INT);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':ip', $ipAddress, PDO::PARAM_STR);
        $stmt->bindValue(':type', $fileType, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function getCountByProject(int $projectId): int {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM downloads WHERE project_id = :pid");
        $stmt->bindValue(':pid', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function getStatsByProject(int $projectId): array {
        $stmt = $this->pdo->prepare("
            SELECT file_type, COUNT(*) as count
            FROM downloads
            WHERE project_id = :pid
            GROUP BY file_type
        ");
        $stmt->bindValue(':pid', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalDownloads(): int {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM downloads");
        return (int) $stmt->fetchColumn();
    }

    public function getTopDownloaded(int $limit = 10): array {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.title, p.author, COUNT(d.id) as download_count
            FROM downloads d
            JOIN projects p ON d.project_id = p.id
            GROUP BY p.id
            ORDER BY download_count DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentDownloads(int $limit = 20): array {
        $stmt = $this->pdo->prepare("
            SELECT d.*, p.title as project_title, u.username
            FROM downloads d
            JOIN projects p ON d.project_id = p.id
            LEFT JOIN users u ON d.user_id = u.id
            ORDER BY d.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
