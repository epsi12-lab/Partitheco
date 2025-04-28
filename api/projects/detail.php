<?php
// api/projects/detail.php
require_once '../../classes/Database.php';
require_once '../../classes/Project.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID manquant']);
    exit;
}

$id = (int) $_GET['id'];

$db = new Database();
$projectObj = new Project($db->getPDO());
$project = $projectObj->getProjectById($id);

if ($project) {
    echo json_encode($project);
} else {
    echo json_encode(['error' => 'Projet non trouvé']);
}
?>
