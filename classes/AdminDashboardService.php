<?php
// classes/AdminDashboardService.php

declare(strict_types=1);

namespace App;

use PDO;

class AdminDashboardService {
    private ProjectRepository $projectRepository;
    private FavoriteRepository $favoriteRepository;
    private User $userObj;
    private PlaylistRepository $playlistRepository;

    public function __construct(PDO $pdo) {
        $this->projectRepository = new ProjectRepository($pdo);
        $this->favoriteRepository = new FavoriteRepository($pdo);
        $this->userObj = new User($pdo);
        $this->playlistRepository = new PlaylistRepository($pdo);
    }

    public function buildDashboardData(array $user): array {
        $userId = (int) $user['id'];

        return [
            'projects' => $this->projectRepository->getByUser($userId),
            'favorites' => $this->favoriteRepository->getByUser($userId),
            'playlists' => $this->playlistRepository->getByUser($userId),
            'userData' => $this->userObj->findByUsername($user['username']),
        ];
    }

    public function updateProfile(array &$sessionUser, string $paroisse, string $role): void {
        $paroisse = trim($paroisse);
        $role = trim($role);

        $this->userObj->updateProfile((int) $sessionUser['id'], $paroisse, $role);
        $sessionUser['paroisse'] = $paroisse;
        $sessionUser['role_choral'] = $role;
    }

    public function createPlaylist(int $userId, string $name, ?string $eventDate = null): bool {
        $name = trim($name);
        $date = trim((string) $eventDate) ?: null;

        if ($name === '') {
            return false;
        }

        $this->playlistRepository->create($userId, $name, null, $date);
        return true;
    }

    public function deletePlaylist(int $playlistId, int $userId): void {
        $this->playlistRepository->delete($playlistId, $userId);
    }
}
