<?php
// classes/Rating.php - Système de notation des partitions

declare(strict_types=1);

namespace App;

use PDO;

class Rating {
    private RatingRepository $repository;

    public function __construct(PDO $pdo) {
        $this->repository = new RatingRepository($pdo);
    }

    public function rate(int $userId, int $projectId, int $score): bool {
        return $this->repository->rate($userId, $projectId, $score);
    }

    public function getUserRating(int $userId, int $projectId): ?int {
        return $this->repository->getUserRating($userId, $projectId);
    }

    public function getAverageRating(int $projectId): ?float {
        return $this->repository->getAverageRating($projectId);
    }

    public function getRatingCount(int $projectId): int {
        return $this->repository->getRatingCount($projectId);
    }
}
