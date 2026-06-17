<?php
// api/projects/reorderPlaylist.php
require_once __DIR__ . '/../../../bootstrap.php';

use App\Database;
use App\PlaylistRepository;

require_auth_api();
require_post_method_api();
verify_api_csrf_or_fail(get_csrf_header_token());

$input = json_decode(file_get_contents('php://input'), true);
$playlistId = (int) ($input['playlist_id'] ?? 0);
$itemIds    = $input['item_ids'] ?? [];

if ($playlistId <= 0 || !is_array($itemIds) || empty($itemIds)) {
    json_error('Parametres invalides', 400);
}

try {
    $db          = new Database();
    $pdo         = $db->getPDO();
    $playlistRepository = new PlaylistRepository($pdo);

    $playlist = $playlistRepository->getById($playlistId);
    if (!$playlist || $playlist['user_id'] !== $_SESSION['user']['id']) {
        json_error('Acces refuse', 403);
    }

    $playlistRepository->reorderItems($playlistId, $itemIds);
    json_success();
} catch (\Throwable $e) {
    json_error('Erreur interne', 500);
}
