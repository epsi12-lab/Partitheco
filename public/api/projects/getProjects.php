<?php
// api/projects/getProjects.php
require_once __DIR__ . '/../../../bootstrap.php';

use App\Database;
use App\ProjectRepository;

try {
    $db         = new Database();
    $projectRepository = new ProjectRepository($db->getPDO());

    $limit  = isset($_GET['limit'])  ? (int) $_GET['limit']  : 5;
    $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

    $projects = $projectRepository->getProjects($limit, $offset);
    json_response($projects);
} catch (\Throwable $e) {
    json_error('Erreur interne', 500);
}
