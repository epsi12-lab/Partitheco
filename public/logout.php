<?php
// logout.php
require_once __DIR__ . '/../bootstrap.php';

use App\AuthService;
use App\Database;

if (!empty($_COOKIE['remember_token'])) {
    $db = new Database();
    $authService = new AuthService($db->getPDO());
    $authService->revokeRememberToken($_COOKIE['remember_token']);
}

$_SESSION = [];
session_destroy();
setcookie('remember_token', '', time() - 3600, '/');
header('Location: login.php');
exit;
?>
