<?php
// api/projects/search.php
header('Content-Type: application/json; charset=utf-8');

try {
    require_once __DIR__ . '/../../classes/Database.php';
    require_once __DIR__ . '/../../classes/Project.php';

    $db      = new Database();
    $pdo     = $db->getPDO();
    $project = new Project($pdo);

    $q = trim($_GET['q'] ?? '');

    if ($q === '') {
        $results = $project->getProjects(1000, 0);
    } else {
        $results = $project->searchProjects($q);
    }

    echo json_encode($results);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error'   => true,
        'message' => $e->getMessage()
    ]);
}
