<?php
// classes/ProjectRepository.php

declare(strict_types=1);

namespace App;

use PDO;

class ProjectRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function create(
        int $userId,
        string $title,
        string $description,
        string $thumbnail,
        ?string $media,
        ?string $author,
        ?string $arranger,
        ?string $genre,
        ?string $tonality,
        ?string $momentMesse = null,
        ?string $tempsLiturgique = null,
        bool $isLiturgical = false,
        ?string $voix = null
    ): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO projects
              (user_id, title, description, thumbnail, media, author, arranger, genre, tonality, moment_messe, temps_liturgique, is_liturgical, voix)
            VALUES
              (:uid, :title, :description, :thumbnail, :media, :author, :arranger, :genre, :tonality, :moment, :temps, :is_liturgical, :voix)
        ");

        return $stmt->execute([
            ':uid' => $userId,
            ':title' => $title,
            ':description' => $description,
            ':thumbnail' => $thumbnail,
            ':media' => $media,
            ':author' => $author,
            ':arranger' => $arranger,
            ':genre' => $genre,
            ':tonality' => $tonality,
            ':moment' => $momentMesse,
            ':temps' => $tempsLiturgique,
            ':is_liturgical' => $isLiturgical,
            ':voix' => $voix,
        ]);
    }

    public function getProjects(int $limit = 5, int $offset = 0): array {
        $stmt = $this->pdo->prepare("
            SELECT p.*, u.username
            FROM projects p
            JOIN users u ON p.user_id = u.id
            ORDER BY p.date_publication DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUser(int $userId): array {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM projects
            WHERE user_id = :uid
            ORDER BY date_publication DESC
        ");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $projectId): ?array {
        $stmt = $this->pdo->prepare("
            SELECT p.*, u.username AS publisher
            FROM projects p
            JOIN users u ON p.user_id = u.id
            WHERE p.id = :id
        ");
        $stmt->execute([':id' => $projectId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Résout un projet à partir d'un item d'import/playlist : par project_id en priorité,
     * sinon par recherche du titre (premier résultat).
     */
    public function resolveByIdOrTitle(array $item): ?array {
        if (!empty($item['project_id'])) {
            $project = $this->getById((int) $item['project_id']);
            if ($project) {
                return $project;
            }
        }

        if (!empty($item['title'])) {
            $results = $this->search((string) $item['title']);
            return $results[0] ?? null;
        }

        return null;
    }

    public function updateByUser(
        int $projectId,
        int $userId,
        string $title,
        string $description,
        ?string $thumbnail = null,
        ?string $media = null,
        ?string $author = null,
        ?string $arranger = null,
        ?string $genre = null,
        ?string $tonality = null,
        ?string $momentMesse = null,
        ?string $tempsLiturgique = null,
        ?bool $isLiturgical = null,
        ?string $voix = null
    ): bool {
        $fields = ['title = :title', 'description = :description'];
        $params = [
            ':title' => $title,
            ':description' => $description,
            ':pid' => $projectId,
            ':uid' => $userId,
        ];

        if ($thumbnail !== null) {
            $fields[] = 'thumbnail = :thumbnail';
            $params[':thumbnail'] = $thumbnail;
        }
        if ($media !== null) {
            $fields[] = 'media = :media';
            $params[':media'] = $media;
        }
        if ($author !== null) {
            $fields[] = 'author = :author';
            $params[':author'] = $author;
        }
        if ($arranger !== null) {
            $fields[] = 'arranger = :arranger';
            $params[':arranger'] = $arranger;
        }
        if ($genre !== null) {
            $fields[] = 'genre = :genre';
            $params[':genre'] = $genre;
        }
        if ($tonality !== null) {
            $fields[] = 'tonality = :tonality';
            $params[':tonality'] = $tonality;
        }
        if ($momentMesse !== null) {
            $fields[] = 'moment_messe = :moment';
            $params[':moment'] = $momentMesse;
        }
        if ($tempsLiturgique !== null) {
            $fields[] = 'temps_liturgique = :temps';
            $params[':temps'] = $tempsLiturgique;
        }
        if ($isLiturgical !== null) {
            $fields[] = 'is_liturgical = :is_liturgical';
            $params[':is_liturgical'] = $isLiturgical;
        }
        if ($voix !== null) {
            $fields[] = 'voix = :voix';
            $params[':voix'] = $voix;
        }

        $sql = "UPDATE projects SET " . implode(', ', $fields) . " WHERE id = :pid AND user_id = :uid";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteByUser(int $projectId, int $userId): bool {
        $stmt = $this->pdo->prepare("
            DELETE FROM projects
            WHERE id = :pid AND user_id = :uid
        ");
        return $stmt->execute([
            ':pid' => $projectId,
            ':uid' => $userId,
        ]);
    }

    public function search(string $query, ?string $moment = null, ?string $temps = null, ?string $voix = null): array {
        $sql = "
            SELECT p.*, u.username
            FROM projects p
            JOIN users u ON p.user_id = u.id
            WHERE (p.title ILIKE :q OR p.description ILIKE :q)
        ";

        $params = [':q' => '%' . $query . '%'];

        if ($moment) {
            $sql .= " AND p.moment_messe = :moment";
            $params[':moment'] = $moment;
        }
        if ($temps) {
            $sql .= " AND p.temps_liturgique = :temps";
            $params[':temps'] = $temps;
        }
        if ($voix) {
            $sql .= " AND p.voix = :voix";
            $params[':voix'] = $voix;
        }

        $sql .= " ORDER BY p.date_publication DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
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

    public function getByPublicationRange(string $startDate, string $endDate, ?string $temps = null): array {
        $sql = "
            SELECT p.*, u.username
            FROM projects p
            JOIN users u ON p.user_id = u.id
            WHERE p.date_publication BETWEEN :start AND :end
        ";

        $params = [
            ':start' => $startDate,
            ':end' => $endDate,
        ];

        if ($temps !== null && $temps !== '') {
            $sql .= " AND p.temps_liturgique = :temps";
            $params[':temps'] = $temps;
        }

        $sql .= " ORDER BY p.moment_messe, p.title";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
