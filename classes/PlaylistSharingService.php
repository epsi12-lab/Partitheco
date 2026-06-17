<?php
// classes/PlaylistSharingService.php

declare(strict_types=1);

namespace App;

use PDO;

class PlaylistSharingService {
    private PlaylistRepository $playlistRepository;

    public function __construct(PDO $pdo) {
        $this->playlistRepository = new PlaylistRepository($pdo);
    }

    public function getSharedPlaylistByToken(string $token): array {
        $token = trim($token);
        if ($token === '') {
            redirect_error('404', 'Lien de partage invalide.');
        }

        $playlist = $this->playlistRepository->getByShareToken($token);
        if (!$playlist) {
            redirect_error('404', 'Liste introuvable ou lien expire.');
        }

        return [
            'playlist' => $playlist,
            'items' => $this->playlistRepository->getItems((int) $playlist['id']),
        ];
    }

    public function getExportData(int $playlistId, ?int $userId = null, ?string $shareToken = null): array {
        if ($playlistId <= 0) {
            json_error('ID de playlist manquant', 400);
        }

        $playlist = $this->playlistRepository->getById($playlistId);
        if (!$playlist) {
            json_error('Playlist introuvable', 404);
        }

        $isOwner = $userId !== null && (int) $playlist['user_id'] === $userId;
        $isShared = !empty($playlist['share_token']) && $shareToken !== null && hash_equals($playlist['share_token'], $shareToken);

        if (!$isOwner && !$isShared) {
            json_error('Acces non autorise', 403);
        }

        return [
            'playlist' => $playlist,
            'items' => $this->playlistRepository->getItems($playlistId),
        ];
    }

    public function buildJsonExport(array $playlist, array $items): string {
        return json_encode([
            'playlist' => [
                'name' => $playlist['name'],
                'description' => $playlist['description'],
                'event_date' => $playlist['event_date'],
                'created_at' => $playlist['created_at'],
            ],
            'items' => array_map(function ($item) {
                return [
                    'title' => $item['title'],
                    'author' => $item['author'] ?? '',
                    'moment_messe' => $item['moment_messe'] ?? '',
                    'temps_liturgique' => $item['temps_liturgique'] ?? '',
                    'note' => $item['note'] ?? '',
                ];
            }, $items),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function buildCsvExport(array $items): string {
        $lines = ["\xEF\xBB\xBFTitre,Auteur,Moment,Temps Liturgique,Note"];

        foreach ($items as $item) {
            $lines[] = implode(',', [
                $this->csvCell($item['title']),
                $this->csvCell($item['author'] ?? ''),
                $this->csvCell($item['moment_messe'] ?? ''),
                $this->csvCell($item['temps_liturgique'] ?? ''),
                $this->csvCell($item['note'] ?? ''),
            ]);
        }

        return implode("\n", $lines) . "\n";
    }

    public function buildTextExport(array $playlist, array $items): string {
        $output = "=== {$playlist['name']} ===\n";

        if (!empty($playlist['description'])) {
            $output .= $playlist['description'] . "\n";
        }
        if (!empty($playlist['event_date'])) {
            $output .= "Date : {$playlist['event_date']}\n";
        }

        $output .= "\nListe des chants :\n";
        $output .= str_repeat('-', 40) . "\n";

        $i = 1;
        foreach ($items as $item) {
            $output .= "{$i}. {$item['title']}";
            if (!empty($item['author'])) {
                $output .= " - {$item['author']}";
            }
            $output .= "\n";
            if (!empty($item['moment_messe'])) {
                $output .= "   Moment : {$item['moment_messe']}\n";
            }
            if (!empty($item['note'])) {
                $output .= "   Note : {$item['note']}\n";
            }
            $i++;
        }

        return $output;
    }

    public function slugify(string $text): string {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        return strtolower($text ?: 'playlist');
    }

    private function csvCell(string $value): string {
        return '"' . str_replace('"', '""', $value) . '"';
    }
}
