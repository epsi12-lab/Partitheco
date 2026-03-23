<?php
// api/rate.php - API pour noter une partition
session_start();
require_once __DIR__ . '/../bootstrap.php';

use App\Database;
use App\RatingRepository;

require_auth_api();
require_post_method_api();
verify_api_csrf_or_fail(get_csrf_header_token());

$data = json_decode(file_get_contents('php://input'), true);
$projectId = (int)($data['project_id'] ?? 0);
$score = (int)($data['score'] ?? 0);

if ($projectId <= 0 || $score < 1 || $score > 5) {
    json_error('Parametres invalides', 400);
}

$db = new Database();
$ratingRepository = new RatingRepository($db->getPDO());

if ($ratingRepository->rate($_SESSION['user']['id'], $projectId, $score)) {
    $avg = $ratingRepository->getAverageRating($projectId);
    $count = $ratingRepository->getRatingCount($projectId);
    json_success([
        'average' => $avg,
        'count' => $count,
        'userRating' => $score
    ]);
} else {
    json_error('Erreur lors de la notation', 500);
}
