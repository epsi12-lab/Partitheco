<?php
// classes/Project.php

namespace App;

use PDO;

class Project {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
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
        $stmt = $this->pdo->prepare("
            INSERT INTO projects
              (user_id, title, description, thumbnail, media, author, arranger, genre, tonality, moment_messe, temps_liturgique, is_liturgical, voix)
            VALUES
              (:uid, :title, :desc, :thumb, :media, :author, :arranger, :genre, :tonality, :moment, :temps, :islit, :voix)
        ");
        $stmt->bindValue(':uid',      $userId,      PDO::PARAM_INT);
        $stmt->bindValue(':title',    $title,       PDO::PARAM_STR);
        $stmt->bindValue(':desc',     $description, PDO::PARAM_STR);
        $stmt->bindValue(':thumb',    $thumbnail,   PDO::PARAM_STR);
        $stmt->bindValue(':media',    $media,       PDO::PARAM_STR);
        $stmt->bindValue(':author',   $author,      PDO::PARAM_STR);
        $stmt->bindValue(':arranger', $arranger,    PDO::PARAM_STR);
        $stmt->bindValue(':genre',    $genre,       PDO::PARAM_STR);
        $stmt->bindValue(':tonality', $tonality,    PDO::PARAM_STR);
        $stmt->bindValue(':moment',   $moment_messe, PDO::PARAM_STR);
        $stmt->bindValue(':temps',    $temps_liturgique, PDO::PARAM_STR);
        $stmt->bindValue(':islit',    $is_liturgical, PDO::PARAM_BOOL);
        $stmt->bindValue(':voix',      $voix,        PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function getProjects(int $limit = 5, int $offset = 0): array {
        $stmt = $this->pdo->prepare("
            SELECT p.*, u.username
            FROM projects p
            JOIN users u ON p.user_id = u.id
            ORDER BY p.date_publication DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit',  (int) $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUser(int $userId): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM projects
            WHERE user_id = :uid
            ORDER BY date_publication DESC
        ");
        $stmt->bindValue(':uid', (int) $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProjectById(int $id): ?array {
        $stmt = $this->pdo->prepare("
            SELECT
              p.*,
              u.username AS publisher
            FROM projects p
            JOIN users    u ON p.user_id = u.id
            WHERE p.id = :id
        ");
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
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
        $fields = ["title = :title", "description = :description"];
        if ($thumbnail !== null) $fields[] = "thumbnail = :thumb";
        if ($media     !== null) $fields[] = "media     = :media";
        if ($author    !== null) $fields[] = "author    = :author";
        if ($arranger  !== null) $fields[] = "arranger  = :arranger";
        if ($genre     !== null) $fields[] = "genre     = :genre";
        if ($tonality  !== null) $fields[] = "tonality  = :tonality";
        if ($moment_messe !== null) $fields[] = "moment_messe = :moment";
        if ($temps_liturgique !== null) $fields[] = "temps_liturgique = :temps";
        if ($is_liturgical !== null) $fields[] = "is_liturgical = :islit";
        if ($voix !== null) $fields[] = "voix = :voix";

        $sql = "UPDATE projects SET " . implode(', ', $fields) .
               " WHERE id = :pid AND user_id = :uid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':title',       $title,       PDO::PARAM_STR);
        $stmt->bindValue(':description', $description, PDO::PARAM_STR);
        if ($thumbnail !== null) $stmt->bindValue(':thumb',    $thumbnail,   PDO::PARAM_STR);
        if ($media     !== null) $stmt->bindValue(':media',    $media,       PDO::PARAM_STR);
        if ($author    !== null) $stmt->bindValue(':author',   $author,      PDO::PARAM_STR);
        if ($arranger  !== null) $stmt->bindValue(':arranger', $arranger,    PDO::PARAM_STR);
        if ($genre     !== null) $stmt->bindValue(':genre',    $genre,       PDO::PARAM_STR);
        if ($tonality  !== null) $stmt->bindValue(':tonality', $tonality,    PDO::PARAM_STR);
        if ($moment_messe !== null) $stmt->bindValue(':moment', $moment_messe, PDO::PARAM_STR);
        if ($temps_liturgique !== null) $stmt->bindValue(':temps', $temps_liturgique, PDO::PARAM_STR);
        if ($is_liturgical !== null) $stmt->bindValue(':islit', $is_liturgical, PDO::PARAM_BOOL);
        if ($voix !== null) $stmt->bindValue(':voix', $voix, PDO::PARAM_STR);
        
        $stmt->bindValue(':pid', (int) $projectId, PDO::PARAM_INT);
        $stmt->bindValue(':uid', (int) $userId,    PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteByUser(int $projectId, int $userId): bool {
        $stmt = $this->pdo->prepare("
            DELETE FROM projects
            WHERE id = :pid AND user_id = :uid
        ");
        $stmt->bindValue(':pid', (int) $projectId, PDO::PARAM_INT);
        $stmt->bindValue(':uid', (int) $userId,    PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function searchProjects(string $query, ?string $moment = null, ?string $temps = null): array {
        $sql = "SELECT p.*, u.username
                FROM projects p
                JOIN users u ON p.user_id = u.id
                WHERE (p.title LIKE :q OR p.description LIKE :q)";
        
        if ($moment) $sql .= " AND p.moment_messe = :moment";
        if ($temps)  $sql .= " AND p.temps_liturgique = :temps";
        
        $sql .= " ORDER BY p.date_publication DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $like = '%' . $query . '%';
        $stmt->bindValue(':q', $like, PDO::PARAM_STR);
        if ($moment) $stmt->bindValue(':moment', $moment, PDO::PARAM_STR);
        if ($temps)  $stmt->bindValue(':temps', $temps, PDO::PARAM_STR);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByTemps(string $temps, int $limit = 5): array {
        $stmt = $this->pdo->prepare("
            SELECT p.*, u.username
            FROM projects p
            JOIN users u ON p.user_id = u.id
            WHERE p.temps_liturgique = :temps
            ORDER BY p.date_publication DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':temps', $temps, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
