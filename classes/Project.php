<?php
// classes/Project.php

class Project {
    private PDO $pdo;

    /**
     * Constructeur : reçoit une instance PDO configurée.
     */
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Insère un nouveau projet en base, lié à l'utilisateur $userId.
     *
     * @param int    $userId      Identifiant de l'utilisateur.
     * @param string $title       Titre du projet.
     * @param string $description Description du projet.
     * @param string $thumbnail   Nom du fichier uploadé.
     * @return bool               True si insertion réussie.
     * @throws Exception          Si le titre est vide ou trop long.
     */
    public function insert(int $userId, string $title, string $description, string $thumbnail): bool {
        if (trim($title) === '' || strlen($title) > 100) {
            throw new Exception("Le titre doit faire entre 1 et 100 caractères.");
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO projects (user_id, title, description, thumbnail)
             VALUES (:uid, :title, :description, :thumb)"
        );
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':title', htmlspecialchars($title, ENT_QUOTES, 'UTF-8'), PDO::PARAM_STR);
        $stmt->bindValue(':description', htmlspecialchars($description, ENT_QUOTES, 'UTF-8'), PDO::PARAM_STR);
        $stmt->bindValue(':thumb', $thumbnail, PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Récupère les derniers projets (tous utilisateurs confondus) avec pagination.
     *
     * @param int $limit  Nombre de projets à récupérer.
     * @param int $offset Décalage pour la pagination.
     * @return array      Liste des projets.
     */
    public function getProjects(int $limit = 5, int $offset = 0): array {
        $stmt = $this->pdo->prepare(
            "SELECT p.*, u.username
             FROM projects p
             JOIN users u ON p.user_id = u.id
             ORDER BY p.date_publication DESC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recherche des projets dont le titre ou la description contient $q.
     *
     * @param string $q Terme à rechercher.
     * @return array    Liste des projets correspondants.
     */
    public function searchProjects(string $q): array {
        $stmt = $this->pdo->prepare(
            "SELECT p.*, u.username
             FROM projects p
             JOIN users u ON p.user_id = u.id
             WHERE p.title LIKE :q OR p.description LIKE :q
             ORDER BY p.date_publication DESC"
        );
        $like = "%{$q}%";
        $stmt->bindValue(':q', $like, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les projets d'un même utilisateur.
     *
     * @param int $userId Identifiant de l'utilisateur.
     * @return array      Liste des projets de cet utilisateur.
     */
    public function getByUser(int $userId): array {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM projects
             WHERE user_id = :uid
             ORDER BY date_publication DESC"
        );
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un projet par son ID.
     *
     * @param int $id Identifiant du projet.
     * @return array|false Données du projet ou false si non trouvé.
     */
    public function getProjectById(int $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Met à jour un projet, si l'utilisateur en est propriétaire.
     *
     * @param int         $projectId   Identifiant du projet.
     * @param int         $userId      Identifiant de l'utilisateur.
     * @param string      $title       Nouveau titre.
     * @param string      $description Nouvelle description.
     * @param string|null $thumbnail   Nouveau fichier (optionnel).
     * @return bool                   True si mise à jour réussie.
     */
    public function updateByUser(int $projectId, int $userId, string $title, string $description, ?string $thumbnail = null): bool {
        $fields = "title = :title, description = :description";
        if ($thumbnail !== null) {
            $fields .= ", thumbnail = :thumb";
        }

        $sql = "UPDATE projects SET {$fields} WHERE id = :pid AND user_id = :uid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':title', htmlspecialchars($title, ENT_QUOTES, 'UTF-8'), PDO::PARAM_STR);
        $stmt->bindValue(':description', htmlspecialchars($description, ENT_QUOTES, 'UTF-8'), PDO::PARAM_STR);
        if ($thumbnail !== null) {
            $stmt->bindValue(':thumb', $thumbnail, PDO::PARAM_STR);
        }
        $stmt->bindValue(':pid', $projectId, PDO::PARAM_INT);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Supprime un projet si l'utilisateur en est le propriétaire.
     *
     * @param int $projectId Identifiant du projet.
     * @param int $userId    Identifiant de l'utilisateur.
     * @return bool          True si suppression réussie.
     */
    public function deleteByUser(int $projectId, int $userId): bool {
        $stmt = $this->pdo->prepare(
            "DELETE FROM projects
             WHERE id = :pid AND user_id = :uid"
        );
        $stmt->bindValue(':pid', $projectId, PDO::PARAM_INT);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
