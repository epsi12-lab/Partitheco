<?php
require_once '../../classes/Database.php';
require_once '../../classes/Project.php';
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
$db         = new Database();
$projectObj = new Project($db->getPDO());

if ($q === '') {
    // Si vide, renvoyer tous (ou retourner vide selon votre choix)
    $projects = $projectObj->getProjects(1000, 0);
} else {
    $projects = $projectObj->searchProjects($q);
}

echo json_encode($projects);
