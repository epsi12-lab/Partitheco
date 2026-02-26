<?php

namespace App;

use PDO;

class Playlist {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function create(int $userId, string $name, ?string $description = null, ?string $eventDate = null): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO playlists (user_id, name, description, event_date)
            VALUES (:uid, :name, :desc, :date)
        ");
        $stmt->bindValue(':uid',  $userId, PDO::PARAM_INT);
        $stmt->bindValue(':name', $name,   PDO::PARAM_STR);
        $stmt->bindValue(':desc', $description, PDO::PARAM_STR);
        $stmt->bindValue(':date', $eventDate, PDO::PARAM_STR);
        $stmt->execute();
        return (int) $this->pdo->lastInsertId();
    }

    public function getByUser(int $userId): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM playlists
            WHERE user_id = :uid
            ORDER BY event_date ASC, created_at DESC
        ");
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM playlists WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function addItem(int $playlistId, int $projectId, ?string $note = null, int $position = 0): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO playlist_items (playlist_id, project_id, note, position)
            VALUES (:plid, :pid, :note, :pos)
        ");
        $stmt->bindValue(':plid', $playlistId, PDO::PARAM_INT);
        $stmt->bindValue(':pid',  $projectId,  PDO::PARAM_INT);
        $stmt->bindValue(':note', $note,       PDO::PARAM_STR);
        $stmt->bindValue(':pos',  $position,   PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getItems(int $playlistId): array {
        $stmt = $this->pdo->prepare("
            SELECT p.*, pi.note, pi.position, pi.id as item_id, u.username as publisher
            FROM projects p
            JOIN playlist_items pi ON p.id = pi.project_id
            JOIN users u ON p.user_id = u.id
            WHERE pi.playlist_id = :plid
            ORDER BY pi.position ASC, pi.id ASC
        ");
        $stmt->bindValue(':plid', $playlistId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function removeItem(int $itemId): bool {
        $stmt = $this->pdo->prepare("DELETE FROM playlist_items WHERE id = :id");
        $stmt->bindValue(':id', $itemId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function updateItemPosition(int $itemId, int $position): bool {
        $stmt = $this->pdo->prepare("UPDATE playlist_items SET position = :pos WHERE id = :id");
        $stmt->bindValue(':pos', $position, PDO::PARAM_INT);
        $stmt->bindValue(':id',  $itemId,   PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function reorderItems(int $playlistId, array $itemIds): void {
        $pos = 1;
        $stmt = $this->pdo->prepare("UPDATE playlist_items SET position = :pos WHERE id = :id AND playlist_id = :plid");
        foreach ($itemIds as $itemId) {
            $stmt->execute([':pos' => $pos, ':id' => (int) $itemId, ':plid' => $playlistId]);
            $pos++;
        }
    }

    public function setShareToken(int $playlistId, int $userId): string {
        $token = bin2hex(random_bytes(16));
        $stmt = $this->pdo->prepare("UPDATE playlists SET share_token = :token WHERE id = :id AND user_id = :uid");
        $stmt->bindValue(':token', $token, PDO::PARAM_STR);
        $stmt->bindValue(':id',    $playlistId, PDO::PARAM_INT);
        $stmt->bindValue(':uid',   $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $token;
    }

    public function getByShareToken(string $token): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM playlists WHERE share_token = :token");
        $stmt->bindValue(':token', $token, PDO::PARAM_STR);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function delete(int $playlistId, int $userId): bool {
        // Suppression des items d'abord (contrainte SQL ou manuelle)
        $this->pdo->prepare("DELETE FROM playlist_items WHERE playlist_id = :plid")
                  ->execute([':plid' => $playlistId]);
        
        $stmt = $this->pdo->prepare("DELETE FROM playlists WHERE id = :id AND user_id = :uid");
        $stmt->bindValue(':id', $playlistId, PDO::PARAM_INT);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
