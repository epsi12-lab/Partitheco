<?php
// api/projects/getProjects.php
require_once __DIR__ . '/../../bootstrap.php';

use App\Database;
use App\Project;

header('Content-Type: application/json');

try {
    $db         = new Database();
    $projectObj = new Project($db->getPDO());

    $limit  = isset($_GET['limit'])  ? (int) $_GET['limit']  : 5;
    $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

    $projects = $projectObj->getProjects($limit, $offset);
    echo json_encode($projects);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
