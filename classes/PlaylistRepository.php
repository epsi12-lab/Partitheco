<?php
// classes/PlaylistRepository.php

namespace App;

use PDO;

class PlaylistRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function create(int $userId, string $name, ?string $description = null, ?string $eventDate = null): int {
        if ($this->isPostgres()) {
            $stmt = $this->pdo->prepare("
                INSERT INTO playlists (user_id, name, description, event_date)
                VALUES (:uid, :name, :desc, :date)
                RETURNING id
            ");
            $stmt->execute([
                ':uid' => $userId,
                ':name' => $name,
                ':desc' => $description,
                ':date' => $eventDate,
            ]);
            return (int) $stmt->fetchColumn();
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO playlists (user_id, name, description, event_date)
            VALUES (:uid, :name, :desc, :date)
        ");
        $stmt->execute([
            ':uid' => $userId,
            ':name' => $name,
            ':desc' => $description,
            ':date' => $eventDate,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function getByUser(int $userId): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM playlists
            WHERE user_id = :uid
            ORDER BY event_date ASC, created_at DESC
        ");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM playlists WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function addItem(int $playlistId, int $projectId, ?string $note = null, int $position = 0): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO playlist_items (playlist_id, project_id, note, position)
            VALUES (:plid, :pid, :note, :pos)
        ");
        return $stmt->execute([
            ':plid' => $playlistId,
            ':pid' => $projectId,
            ':note' => $note,
            ':pos' => $position,
        ]);
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
        $stmt->execute([':plid' => $playlistId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function removeItem(int $itemId): bool {
        $stmt = $this->pdo->prepare("DELETE FROM playlist_items WHERE id = :id");
        return $stmt->execute([':id' => $itemId]);
    }

    public function updateItemPosition(int $itemId, int $position): bool {
        $stmt = $this->pdo->prepare("UPDATE playlist_items SET position = :pos WHERE id = :id");
        return $stmt->execute([':pos' => $position, ':id' => $itemId]);
    }

    public function reorderItems(int $playlistId, array $itemIds): void {
        $position = 1;
        $stmt = $this->pdo->prepare("
            UPDATE playlist_items
            SET position = :pos
            WHERE id = :id AND playlist_id = :plid
        ");

        foreach ($itemIds as $itemId) {
            $stmt->execute([
                ':pos' => $position,
                ':id' => (int) $itemId,
                ':plid' => $playlistId,
            ]);
            $position++;
        }
    }

    public function setShareToken(int $playlistId, int $userId): string {
        $token = bin2hex(random_bytes(16));
        $stmt = $this->pdo->prepare("
            UPDATE playlists
            SET share_token = :token
            WHERE id = :id AND user_id = :uid
        ");
        $stmt->execute([
            ':token' => $token,
            ':id' => $playlistId,
            ':uid' => $userId,
        ]);
        return $token;
    }

    public function getByShareToken(string $token): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM playlists WHERE share_token = :token");
        $stmt->execute([':token' => $token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function delete(int $playlistId, int $userId): bool {
        $this->pdo->prepare("DELETE FROM playlist_items WHERE playlist_id = :plid")
            ->execute([':plid' => $playlistId]);

        $stmt = $this->pdo->prepare("
            DELETE FROM playlists
            WHERE id = :id AND user_id = :uid
        ");
        return $stmt->execute([
            ':id' => $playlistId,
            ':uid' => $userId,
        ]);
    }

    private function isPostgres(): bool {
        return $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql';
    }
}
