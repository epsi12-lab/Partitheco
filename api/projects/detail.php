<?php
// api/projects/detail.php
require_once __DIR__ . '/../../bootstrap.php';

use App\Database;
use App\ProjectRepository;

if (!isset($_GET['id'])) {
    json_error('ID manquant', 400);
}

$id = (int) $_GET['id'];

$db = new Database();
$projectRepository = new ProjectRepository($db->getPDO());
$project = $projectRepository->getById($id);

if ($project) {
    json_response($project);
} else {
    json_error('Projet non trouve', 404);
}
