<?php
// classes/PlaylistImportService.php

namespace App;

class PlaylistImportService {
    private PlaylistRepository $playlistRepository;
    private ProjectRepository $projectRepository;

    public function __construct(PlaylistRepository $playlistRepository, ProjectRepository $projectRepository) {
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

        $playlistId = $this->playlistRepository->create($userId, $name, $description, $eventDate);
        if (!$playlistId) {
            return ['success' => false, 'error' => 'Erreur creation playlist'];
        }

        $added = 0;
        $notFound = [];

        foreach (($data['items'] ?? []) as $item) {
            $projectId = $this->resolveProjectId((array) $item);
            if ($projectId !== null) {
                $this->playlistRepository->addItem($playlistId, $projectId, $item['note'] ?? null);
                $added++;
                continue;
            }

            $notFound[] = $item['title'] ?? $item['project_id'] ?? 'inconnu';
        }

        return [
            'success' => true,
            'playlist_id' => $playlistId,
            'added' => $added,
            'not_found' => $notFound,
            'message' => "Playlist créée avec $added chant(s)",
        ];
    }

    private function resolveProjectId(array $item): ?int {
        if (isset($item['project_id'])) {
            $project = $this->projectRepository->getById((int) $item['project_id']);
            if ($project) {
                return (int) $project['id'];
            }
        }

        if (!empty($item['title'])) {
            $results = $this->projectRepository->search($item['title']);
            if (!empty($results[0]['id'])) {
                return (int) $results[0]['id'];
            }
        }

        return null;
    }
}
