<?php
// classes/Download.php

namespace App;

use PDO;

class Download {
    private DownloadRepository $repository;

    public function __construct(PDO $pdo) {
        $this->repository = new DownloadRepository($pdo);
    }

    public function record(int $projectId, ?int $userId, string $ipAddress, string $fileType): bool {
        return $this->repository->record($projectId, $userId, $ipAddress, $fileType);
    }

    public function getCountByProject(int $projectId): int {
        return $this->repository->getCountByProject($projectId);
    }

    public function getStatsByProject(int $projectId): array {
        return $this->repository->getStatsByProject($projectId);
    }

    public function getTotalDownloads(): int {
        return $this->repository->getTotalDownloads();
    }

    public function getTopDownloaded(int $limit = 10): array {
        return $this->repository->getTopDownloaded($limit);
    }

    public function getRecentDownloads(int $limit = 20): array {
        return $this->repository->getRecentDownloads($limit);
    }
}
