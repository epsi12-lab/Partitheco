<?php
// api/download.php - Enregistre et sert les téléchargements
session_start();
require_once __DIR__ . '/../bootstrap.php';

use App\Database;
use App\Download;
use App\Project;

$db = new Database();
$pdo = $db->getPDO();
$downloadObj = new Download($pdo);
$projectObj = new Project($pdo);

$projectId = (int) ($_GET['id'] ?? 0);
$fileType = $_GET['type'] ?? 'pdf'; // pdf, image, audio, video

if (!$projectId) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de projet manquant']);
    exit;
}

$project = $projectObj->getProjectById($projectId);
if (!$project) {
    http_response_code(404);
    echo json_encode(['error' => 'Projet introuvable']);
    exit;
}

// Enregistrer le téléchargement
$userId = $_SESSION['user']['id'] ?? null;
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$downloadObj->record($projectId, $userId, $ipAddress, $fileType);

// Déterminer le fichier à servir
$file = null;
$filename = null;

if ($fileType === 'pdf' || $fileType === 'image') {
    $file = __DIR__ . '/../assets/img/' . $project['thumbnail'];
    $filename = $project['title'] . '.' . pathinfo($project['thumbnail'], PATHINFO_EXTENSION);
} elseif (($fileType === 'audio' || $fileType === 'video') && $project['media']) {
    $file = __DIR__ . '/../assets/img/' . $project['media'];
    $filename = $project['title'] . '.' . pathinfo($project['media'], PATHINFO_EXTENSION);
}

if ($file && file_exists($file)) {
    // Servir le fichier
    $mime = mime_content_type($file);
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
} else {
    // Retourner juste le compteur si pas de téléchargement direct
    $count = $downloadObj->getCountByProject($projectId);
    echo json_encode(['success' => true, 'downloads' => $count]);
}
