<?php
// api/projects/search.php
require_once __DIR__ . '/../../bootstrap.php';

use App\Database;
use App\Project;

header('Content-Type: application/json; charset=utf-8');

try {
    $db      = new Database();
    $pdo     = $db->getPDO();
    $project = new Project($pdo);

    $q = trim($_GET['q'] ?? '');
    $moment = trim($_GET['moment'] ?? '') ?: null;
    $temps = trim($_GET['temps'] ?? '') ?: null;

    if ($q === '' && $moment === null && $temps === null) {
        $results = $project->getProjects(1000, 0);
    } else {
        $results = $project->searchProjects($q, $moment, $temps);
    }

    echo json_encode($results);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error'   => true,
        'message' => $e->getMessage()
    ]);
}
