<?php
// classes/PlaylistPageService.php

declare(strict_types=1);

namespace App;

use PDO;

class PlaylistPageService {
    private PDO $pdo;
    private PlaylistRepository $playlistRepository;
    private ProjectRepository $projectRepository;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->playlistRepository = new PlaylistRepository($pdo);
        $this->projectRepository = new ProjectRepository($pdo);
    }

    public function getAccessiblePlaylist(int $playlistId, int $userId): array {
        $playlist = $this->playlistRepository->getById($playlistId);
        if (!$playlist || (int) $playlist['user_id'] !== $userId) {
            redirect_error('403', 'Acces refuse a cette liste.');
        }

        return $playlist;
    }

    public function buildPageData(int $playlistId, int $userId): array {
        $playlist = $this->getAccessiblePlaylist($playlistId, $userId);

        return [
            'playlist' => $playlist,
            'items' => $this->playlistRepository->getItems($playlistId),
        ];
    }

    public function addItem(int $playlistId, int $projectId, ?string $note = null): bool {
        if ($projectId <= 0) {
            return false;
        }

        return $this->playlistRepository->addItem($playlistId, $projectId, $this->normalizeNote($note));
    }

    public function removeItem(int $itemId): bool {
        if ($itemId <= 0) {
            return false;
        }

        return $this->playlistRepository->removeItem($itemId);
    }

    public function generateShareToken(int $playlistId, int $userId): string {
        return $this->playlistRepository->setShareToken($playlistId, $userId);
    }

    public function importFromJsonFile(int $playlistId, array $file): array {
        if (empty($file['tmp_name'])) {
            return ['imported' => 0, 'not_found' => 0];
        }

        $jsonContent = file_get_contents($file['tmp_name']);
        $importData = json_decode($jsonContent, true);
        $importedCount = 0;
        $notFoundCount = 0;

        if (!$importData || !isset($importData['items']) || !is_array($importData['items'])) {
            return ['imported' => 0, 'not_found' => 0];
        }

        $this->pdo->beginTransaction();
        try {
            foreach ($importData['items'] as $item) {
                $project = $this->projectRepository->resolveByIdOrTitle((array) $item);
                if ($project) {
                    $this->playlistRepository->addItem($playlistId, (int) $project['id'], $this->normalizeNote($item['note'] ?? null));
                    $importedCount++;
                } else {
                    $notFoundCount++;
                }
            }
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return ['imported' => $importedCount, 'not_found' => $notFoundCount];
    }

    private function normalizeNote(?string $note): ?string {
        $note = trim((string) $note);
        return $note !== '' ? $note : null;
    }
}
