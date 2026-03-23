<?php
// classes/Project.php

namespace App;

use PDO;

class Project {
    private ProjectRepository $repository;

    public function __construct(PDO $pdo) {
        $this->repository = new ProjectRepository($pdo);
    }

    public function insert(
        int $userId,
        string $title,
        string $description,
        string $thumbnail,
        ?string $media,
        ?string $author,
        ?string $arranger,
        ?string $genre,
        ?string $tonality,
        ?string $moment_messe = null,
        ?string $temps_liturgique = null,
        bool $is_liturgical = false,
        ?string $voix = null
    ): bool {
        return $this->repository->create(
            $userId,
            $title,
            $description,
            $thumbnail,
            $media,
            $author,
            $arranger,
            $genre,
            $tonality,
            $moment_messe,
            $temps_liturgique,
            $is_liturgical,
            $voix
        );
    }

    public function getProjects(int $limit = 5, int $offset = 0): array {
        return $this->repository->getProjects($limit, $offset);
    }

    public function getByUser(int $userId): array {
        return $this->repository->getByUser($userId);
    }

    public function getProjectById(int $id): ?array {
        return $this->repository->getById($id);
    }

    public function updateByUser(
        int $projectId,
        int $userId,
        string $title,
        string $description,
        ?string $thumbnail = null,
        ?string $media     = null,
        ?string $author    = null,
        ?string $arranger  = null,
        ?string $genre     = null,
        ?string $tonality  = null,
        ?string $moment_messe = null,
        ?string $temps_liturgique = null,
        ?bool $is_liturgical = null,
        ?string $voix = null
    ): bool {
        return $this->repository->updateByUser(
            $projectId,
            $userId,
            $title,
            $description,
            $thumbnail,
            $media,
            $author,
            $arranger,
            $genre,
            $tonality,
            $moment_messe,
            $temps_liturgique,
            $is_liturgical,
            $voix
        );
    }

    public function deleteByUser(int $projectId, int $userId): bool {
        return $this->repository->deleteByUser($projectId, $userId);
    }

    public function searchProjects(string $query, ?string $moment = null, ?string $temps = null): array {
        return $this->repository->search($query, $moment, $temps);
    }

    public function getByTemps(string $temps, int $limit = 5): array {
        return $this->repository->getByTemps($temps, $limit);
    }
}
