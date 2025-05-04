<?php
// classes/Project.php

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
        ?string $tonality
    ): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO projects
              (user_id, title, description, thumbnail, media, author, arranger, genre, tonality)
            VALUES
              (:uid, :title, :desc, :thumb, :media, :author, :arranger, :genre, :tonality)
        ");
        $stmt->bindValue(':uid',      $userId,      PDO::PARAM_INT);
        $stmt->bindValue(':title',    htmlspecialchars($title,    ENT_QUOTES), PDO::PARAM_STR);
        $stmt->bindValue(':desc',     htmlspecialchars($description,ENT_QUOTES), PDO::PARAM_STR);
        $stmt->bindValue(':thumb',    $thumbnail,   PDO::PARAM_STR);
        $stmt->bindValue(':media',    $media,       PDO::PARAM_STR);
        $stmt->bindValue(':author',   $author,      PDO::PARAM_STR);
        $stmt->bindValue(':arranger', $arranger,    PDO::PARAM_STR);
        $stmt->bindValue(':genre',    $genre,       PDO::PARAM_STR);
        $stmt->bindValue(':tonality', $tonality,    PDO::PARAM_STR);
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
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUser(int $userId): array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM projects
            WHERE user_id = :uid
            ORDER BY date_publication DESC
        ");
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProjectById(int $id) {
        $stmt = $this->pdo->prepare("
            SELECT
              p.*,
              u.username AS publisher
            FROM projects p
            JOIN users    u ON p.user_id = u.id
            WHERE p.id = :id
        ");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
        ?string $tonality  = null
    ): bool {
        $fields = ["title = :title", "description = :description"];
        if ($thumbnail !== null) $fields[] = "thumbnail = :thumb";
        if ($media     !== null) $fields[] = "media     = :media";
        if ($author    !== null) $fields[] = "author    = :author";
        if ($arranger  !== null) $fields[] = "arranger  = :arranger";
        if ($genre     !== null) $fields[] = "genre     = :genre";
        if ($tonality  !== null) $fields[] = "tonality  = :tonality";

        $sql = "UPDATE projects SET " . implode(', ', $fields) .
               " WHERE id = :pid AND user_id = :uid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':title',       htmlspecialchars($title,    ENT_QUOTES), PDO::PARAM_STR);
        $stmt->bindValue(':description', htmlspecialchars($description,ENT_QUOTES), PDO::PARAM_STR);
        if ($thumbnail !== null) $stmt->bindValue(':thumb',    $thumbnail,   PDO::PARAM_STR);
        if ($media     !== null) $stmt->bindValue(':media',    $media,       PDO::PARAM_STR);
        if ($author    !== null) $stmt->bindValue(':author',   $author,      PDO::PARAM_STR);
        if ($arranger  !== null) $stmt->bindValue(':arranger', $arranger,    PDO::PARAM_STR);
        if ($genre     !== null) $stmt->bindValue(':genre',    $genre,       PDO::PARAM_STR);
        if ($tonality  !== null) $stmt->bindValue(':tonality', $tonality,    PDO::PARAM_STR);
        $stmt->bindValue(':pid', $projectId, PDO::PARAM_INT);
        $stmt->bindValue(':uid', $userId,    PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteByUser(int $projectId, int $userId): bool {
        $stmt = $this->pdo->prepare("
            DELETE FROM projects
            WHERE id = :pid AND user_id = :uid
        ");
        $stmt->bindValue(':pid', $projectId, PDO::PARAM_INT);
        $stmt->bindValue(':uid', $userId,    PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function searchProjects(string $query): array {
        $stmt = $this->pdo->prepare("
            SELECT p.*, u.username
            FROM projects p
            JOIN users u ON p.user_id = u.id
            WHERE p.title LIKE :q
               OR p.description LIKE :q
            ORDER BY p.date_publication DESC
        ");
        $like = '%' . $query . '%';
        $stmt->bindValue(':q', $like, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
