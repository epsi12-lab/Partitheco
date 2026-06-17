<?php
// api/download.php - Enregistre et sert les téléchargements
require_once __DIR__ . '/../../bootstrap.php';

use App\Database;
use App\DownloadRepository;
use App\DownloadService;
use App\ProjectRepository;

$db = new Database();
$pdo = $db->getPDO();
$downloadService = new DownloadService(
    new DownloadRepository($pdo),
    new ProjectRepository($pdo)
);

$projectId = (int) ($_GET['id'] ?? 0);
$fileType = $_GET['type'] ?? 'pdf'; // pdf, image, audio, video

$userId = $_SESSION['user']['id'] ?? null;
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$result = $downloadService->handleDownloadRequest($projectId, $fileType, $userId, $ipAddress);

if (!$result['success']) {
    json_error($result['error'], $result['status']);
}

if (!empty($result['file'])) {
    file_download_response($result['file'], $result['filename']);
}

json_success(['downloads' => $result['downloads'] ?? 0]);
