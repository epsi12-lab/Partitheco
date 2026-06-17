<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

class Favorite {
    private FavoriteRepository $repository;

    public function __construct(PDO $pdo) {
        $this->repository = new FavoriteRepository($pdo);
    }

    public function add(int $userId, int $projectId): bool {
        return $this->repository->add($userId, $projectId);
    }

    public function remove(int $userId, int $projectId): bool {
        return $this->repository->remove($userId, $projectId);
    }

    public function isFavorite(int $userId, int $projectId): bool {
        return $this->repository->isFavorite($userId, $projectId);
    }

    public function getByUser(int $userId): array {
        return $this->repository->getByUser($userId);
    }
}
