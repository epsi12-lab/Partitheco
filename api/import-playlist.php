<?php
// api/import-playlist.php - Import de playlists depuis JSON
session_start();
require_once __DIR__ . '/../bootstrap.php';

use App\Database;
use App\Playlist;
use App\Project;

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Non connecté']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

$db = new Database();
$pdo = $db->getPDO();
$playlistObj = new Playlist($pdo);
$projectObj = new Project($pdo);
$userId = $_SESSION['user']['id'];

// Récupérer le JSON
$jsonData = null;

if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $jsonData = file_get_contents($_FILES['file']['tmp_name']);
} elseif (isset($_POST['json'])) {
    $jsonData = $_POST['json'];
}

if (!$jsonData) {
    echo json_encode(['success' => false, 'error' => 'Aucune donnée JSON fournie']);
    exit;
}

$data = json_decode($jsonData, true);
if (!$data) {
    echo json_encode(['success' => false, 'error' => 'JSON invalide']);
    exit;
}

// Créer la playlist
$name = $data['name'] ?? 'Playlist importée ' . date('d/m/Y H:i');
$description = $data['description'] ?? null;
$eventDate = $data['event_date'] ?? null;

$playlistId = $playlistObj->create($userId, $name, $description, $eventDate);

if (!$playlistId) {
    echo json_encode(['success' => false, 'error' => 'Erreur création playlist']);
    exit;
}

// Ajouter les items
$added = 0;
$notFound = [];

if (isset($data['items']) && is_array($data['items'])) {
    foreach ($data['items'] as $item) {
        $projectId = null;
        
        // Chercher par ID
        if (isset($item['project_id'])) {
            $project = $projectObj->getProjectById((int)$item['project_id']);
            if ($project) $projectId = $project['id'];
        }
        
        // Chercher par titre si pas trouvé
        if (!$projectId && isset($item['title'])) {
            $results = $projectObj->searchProjects($item['title']);
            if (!empty($results)) {
                $projectId = $results[0]['id'];
            }
        }
        
        if ($projectId) {
            $note = $item['note'] ?? null;
            $playlistObj->addItem($playlistId, $projectId, $note);
            $added++;
        } else {
            $notFound[] = $item['title'] ?? $item['project_id'] ?? 'inconnu';
        }
    }
}

echo json_encode([
    'success' => true,
    'playlist_id' => $playlistId,
    'added' => $added,
    'not_found' => $notFound,
    'message' => "Playlist créée avec $added chant(s)"
]);
