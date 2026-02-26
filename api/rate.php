<?php
// api/rate.php - API pour noter une partition
session_start();
require_once __DIR__ . '/../bootstrap.php';

use App\Database;
use App\Rating;

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non connecté']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$projectId = (int)($data['project_id'] ?? 0);
$score = (int)($data['score'] ?? 0);

if ($projectId <= 0 || $score < 1 || $score > 5) {
    http_response_code(400);
    echo json_encode(['error' => 'Paramètres invalides']);
    exit;
}

$db = new Database();
$ratingObj = new Rating($db->getPDO());

if ($ratingObj->rate($_SESSION['user']['id'], $projectId, $score)) {
    $avg = $ratingObj->getAverageRating($projectId);
    $count = $ratingObj->getRatingCount($projectId);
    echo json_encode([
        'success' => true,
        'average' => $avg,
        'count' => $count,
        'userRating' => $score
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la notation']);
}
