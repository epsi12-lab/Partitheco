<?php
// delete.php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$projectId = (int) ($_GET['id'] ?? 0);
$lang      = $_GET['lang'] ?? 'fr';
$userId    = $_SESSION['user']['id'];

require_once 'classes/Database.php';
require_once 'classes/Project.php';

$db         = new Database();
$projectObj = new Project($db->getPDO());
$projectObj->deleteByUser($projectId, $userId);

header("Location: admin.php?lang=$lang");
exit;
?>
