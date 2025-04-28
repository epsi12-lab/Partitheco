<?php
require_once '../../classes/Database.php';
require_once '../../classes/Project.php';
header('Content-Type: application/json');

$db         = new Database();
$projectObj = new Project($db->getPDO());

$limit  = isset($_GET['limit'])  ? (int) $_GET['limit']  : 5;
$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

$projects = $projectObj->getProjects($limit, $offset);
echo json_encode($projects);
