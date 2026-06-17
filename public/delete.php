<?php
// delete.php
require_once __DIR__ . '/../bootstrap.php';

use App\Database;
use App\ProjectRepository;

require_auth_redirect();

require_post_method();
verify_csrf_or_fail($_POST['csrf_token'] ?? null);

$projectId = (int) ($_POST['id'] ?? 0);
$lang      = $_GET['lang'] ?? 'fr';
$userId    = $_SESSION['user']['id'];

$db         = new Database();
$projectRepository = new ProjectRepository($db->getPDO());
$projectRepository->deleteByUser($projectId, $userId);

header("Location: admin.php?lang=$lang");
exit;
?>
