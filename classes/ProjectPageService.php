<?php
// classes/ProjectPageService.php

namespace App;

use PDO;

class ProjectPageService {
    private ProjectRepository $projectRepository;
    private CommentRepository $commentRepository;
    private FavoriteRepository $favoriteRepository;
    private DownloadRepository $downloadRepository;
    private RatingRepository $ratingRepository;
    private PlaylistRepository $playlistRepository;

    public function __construct(PDO $pdo) {
        $this->projectRepository = new ProjectRepository($pdo);
        $this->commentRepository = new CommentRepository($pdo);
        $this->favoriteRepository = new FavoriteRepository($pdo);
        $this->downloadRepository = new DownloadRepository($pdo);
        $this->ratingRepository = new RatingRepository($pdo);
        $this->playlistRepository = new PlaylistRepository($pdo);
    }

    public function buildPageData(int $projectId, ?array $user = null): array {
        $project = $this->projectRepository->getById($projectId);
        if (!$project) {
            redirect_error('404', 'Projet introuvable.');
        }

        $userId = $user['id'] ?? null;

        return [
            'project' => $project,
            'avgRating' => $this->ratingRepository->getAverageRating($projectId),
            'ratingCount' => $this->ratingRepository->getRatingCount($projectId),
            'userRating' => $userId ? $this->ratingRepository->getUserRating($userId, $projectId) : null,
            'isFavorite' => $userId ? $this->favoriteRepository->isFavorite($userId, $projectId) : false,
            'comments' => $this->commentRepository->getByProject($projectId),
            'downloadCount' => $this->downloadRepository->getCountByProject($projectId),
            'userPlaylists' => $userId ? $this->playlistRepository->getByUser($userId) : [],
        ];
    }

    public function toggleFavorite(int $projectId, array $user, bool $isFavorite): void {
        if ($isFavorite) {
            $this->favoriteRepository->remove($user['id'], $projectId);
            return;
        }

        $this->favoriteRepository->add($user['id'], $projectId);
    }

    public function addComment(int $projectId, string $author, string $content): bool {
        $author = trim($author);
        $content = trim($content);

        if ($author === '' || $content === '') {
            return false;
        }

        return $this->commentRepository->insert($projectId, $author, $content);
    }
}
