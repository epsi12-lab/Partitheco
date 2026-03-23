<?php
// api/import-playlist.php - Import de playlists depuis JSON
session_start();
require_once __DIR__ . '/../bootstrap.php';

use App\Database;
use App\PlaylistImportService;
use App\PlaylistRepository;
use App\ProjectRepository;

require_auth_api();
require_post_method_api();
verify_api_csrf_or_fail($_POST['csrf_token'] ?? get_csrf_header_token());

$db = new Database();
$pdo = $db->getPDO();
$playlistRepository = new PlaylistRepository($pdo);
$projectRepository = new ProjectRepository($pdo);
$userId = $_SESSION['user']['id'];
$playlistImportService = new PlaylistImportService($playlistRepository, $projectRepository);

// Récupérer le JSON
$jsonData = null;

if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $jsonData = file_get_contents($_FILES['file']['tmp_name']);
} elseif (isset($_POST['json'])) {
    $jsonData = $_POST['json'];
}

if (!$jsonData) {
    json_error('Aucune donnee JSON fournie', 400, ['success' => false]);
}

$result = $playlistImportService->importFromJsonPayload($userId, $jsonData);
if (!$result['success']) {
    $status = $result['error'] === 'JSON invalide' ? 400 : 500;
    json_error($result['error'], $status, ['success' => false]);
}

json_success([
    'playlist_id' => $result['playlist_id'],
    'added' => $result['added'],
    'not_found' => $result['not_found'],
    'message' => $result['message']
]);
