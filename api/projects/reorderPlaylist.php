<?php
// api/projects/reorderPlaylist.php
session_start();
require_once __DIR__ . '/../../bootstrap.php';

use App\Database;
use App\Playlist;

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$playlistId = (int) ($input['playlist_id'] ?? 0);
$itemIds    = $input['item_ids'] ?? [];

if ($playlistId <= 0 || !is_array($itemIds) || empty($itemIds)) {
    http_response_code(400);
    echo json_encode(['error' => 'Paramètres invalides']);
    exit;
}

try {
    $db          = new Database();
    $pdo         = $db->getPDO();
    $playlistObj = new Playlist($pdo);

    $playlist = $playlistObj->getById($playlistId);
    if (!$playlist || $playlist['user_id'] !== $_SESSION['user']['id']) {
        http_response_code(403);
        echo json_encode(['error' => 'Accès refusé']);
        exit;
    }

    $playlistObj->reorderItems($playlistId, $itemIds);
    echo json_encode(['success' => true]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
