<?php
// api/export-playlist.php - Export de playlist en PDF ou JSON
session_start();
require_once __DIR__ . '/../bootstrap.php';

use App\Database;
use App\Playlist;

$db = new Database();
$pdo = $db->getPDO();
$playlistObj = new Playlist($pdo);

$playlistId = (int) ($_GET['id'] ?? 0);
$format = $_GET['format'] ?? 'json'; // json, csv, txt

if (!$playlistId) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de playlist manquant']);
    exit;
}

$playlist = $playlistObj->getById($playlistId);
if (!$playlist) {
    http_response_code(404);
    echo json_encode(['error' => 'Playlist introuvable']);
    exit;
}

// Vérifier les droits (propriétaire ou playlist partagée)
$userId = $_SESSION['user']['id'] ?? null;
$isOwner = $userId && $playlist['user_id'] == $userId;
$isShared = !empty($playlist['share_token']) && ($_GET['token'] ?? '') === $playlist['share_token'];

if (!$isOwner && !$isShared) {
    http_response_code(403);
    echo json_encode(['error' => 'Accès non autorisé']);
    exit;
}

$items = $playlistObj->getItems($playlistId);

switch ($format) {
    case 'json':
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . slugify($playlist['name']) . '.json"');
        echo json_encode([
            'playlist' => [
                'name' => $playlist['name'],
                'description' => $playlist['description'],
                'event_date' => $playlist['event_date'],
                'created_at' => $playlist['created_at']
            ],
            'items' => array_map(function($item) {
                return [
                    'title' => $item['title'],
                    'author' => $item['author'] ?? '',
                    'moment_messe' => $item['moment_messe'] ?? '',
                    'temps_liturgique' => $item['temps_liturgique'] ?? '',
                    'note' => $item['note'] ?? ''
                ];
            }, $items)
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        break;

    case 'csv':
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . slugify($playlist['name']) . '.csv"');
        echo "\xEF\xBB\xBF"; // BOM UTF-8
        echo "Titre,Auteur,Moment,Temps Liturgique,Note\n";
        foreach ($items as $item) {
            echo '"' . str_replace('"', '""', $item['title']) . '",';
            echo '"' . str_replace('"', '""', $item['author'] ?? '') . '",';
            echo '"' . str_replace('"', '""', $item['moment_messe'] ?? '') . '",';
            echo '"' . str_replace('"', '""', $item['temps_liturgique'] ?? '') . '",';
            echo '"' . str_replace('"', '""', $item['note'] ?? '') . '"';
            echo "\n";
        }
        break;

    case 'txt':
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . slugify($playlist['name']) . '.txt"');
        echo "=== " . $playlist['name'] . " ===\n";
        if ($playlist['description']) echo $playlist['description'] . "\n";
        if ($playlist['event_date']) echo "Date : " . $playlist['event_date'] . "\n";
        echo "\n";
        echo "Liste des chants :\n";
        echo str_repeat("-", 40) . "\n";
        $i = 1;
        foreach ($items as $item) {
            echo "{$i}. {$item['title']}";
            if ($item['author']) echo " - {$item['author']}";
            echo "\n";
            if ($item['moment_messe']) echo "   Moment : {$item['moment_messe']}\n";
            if ($item['note']) echo "   Note : {$item['note']}\n";
            $i++;
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Format non supporté']);
}

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text) ?: 'playlist';
}
