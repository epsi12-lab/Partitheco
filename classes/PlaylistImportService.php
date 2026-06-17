<?php
// classes/PlaylistImportService.php

declare(strict_types=1);

namespace App;

use PDO;

class PlaylistImportService {
    private PDO $pdo;
    private PlaylistRepository $playlistRepository;
    private ProjectRepository $projectRepository;

    public function __construct(PDO $pdo, PlaylistRepository $playlistRepository, ProjectRepository $projectRepository) {
        $this->pdo = $pdo;
        $this->playlistRepository = $playlistRepository;
        $this->projectRepository = $projectRepository;
    }

    public function importFromJsonPayload(int $userId, string $jsonData): array {
        $data = json_decode($jsonData, true);
        if (!is_array($data)) {
            return ['success' => false, 'error' => 'JSON invalide'];
        }

        $name = $data['name'] ?? ('Playlist importée ' . date('d/m/Y H:i'));
        $description = $data['description'] ?? null;
        $eventDate = $data['event_date'] ?? null;

        $this->pdo->beginTransaction();
        try {
            $playlistId = $this->playlistRepository->create($userId, $name, $description, $eventDate);
            if (!$playlistId) {
                $this->pdo->rollBack();
                return ['success' => false, 'error' => 'Erreur creation playlist'];
            }

            $added = 0;
            $notFound = [];

            foreach (($data['items'] ?? []) as $item) {
                $project = $this->projectRepository->resolveByIdOrTitle((array) $item);
                if ($project !== null) {
                    $this->playlistRepository->addItem($playlistId, (int) $project['id'], $item['note'] ?? null);
                    $added++;
                    continue;
                }

                $notFound[] = $item['title'] ?? $item['project_id'] ?? 'inconnu';
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return [
            'success' => true,
            'playlist_id' => $playlistId,
            'added' => $added,
            'not_found' => $notFound,
            'message' => "Playlist créée avec $added chant(s)",
        ];
    }
}
