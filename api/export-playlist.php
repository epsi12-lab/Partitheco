<?php
// api/export-playlist.php - Export de playlist en PDF ou JSON
session_start();
require_once __DIR__ . '/../bootstrap.php';

use App\Database;
use App\PlaylistSharingService;

$db = new Database();
$pdo = $db->getPDO();
$sharingService = new PlaylistSharingService($pdo);

$playlistId = (int) ($_GET['id'] ?? 0);
$format = $_GET['format'] ?? 'json'; // json, csv, txt
$userId = $_SESSION['user']['id'] ?? null;
$exportData = $sharingService->getExportData(
    $playlistId,
    $userId ? (int) $userId : null,
    $_GET['token'] ?? null
);
$playlist = $exportData['playlist'];
$items = $exportData['items'];
$filenameBase = $sharingService->slugify($playlist['name']);

switch ($format) {
    case 'json':
        download_response(
            $sharingService->buildJsonExport($playlist, $items),
            'application/json; charset=utf-8',
            $filenameBase . '.json'
        );

    case 'csv':
        download_response(
            $sharingService->buildCsvExport($items),
            'text/csv; charset=utf-8',
            $filenameBase . '.csv'
        );

    case 'txt':
        download_response(
            $sharingService->buildTextExport($playlist, $items),
            'text/plain; charset=utf-8',
            $filenameBase . '.txt'
        );

    default:
        json_error('Format non supporte', 400);
}
