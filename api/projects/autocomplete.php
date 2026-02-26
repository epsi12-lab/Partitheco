<?php
// api/projects/autocomplete.php
require_once __DIR__ . '/../../bootstrap.php';

use App\Database;

header('Content-Type: application/json; charset=utf-8');

try {
    $q = trim($_GET['q'] ?? '');
    if (strlen($q) < 2) {
        echo json_encode([]);
        exit;
    }

    $db  = new Database();
    $pdo = $db->getPDO();

    $stmt = $pdo->prepare("
        SELECT id, title, author, moment_messe
        FROM projects
        WHERE title LIKE :q OR author LIKE :q
        ORDER BY title ASC
        LIMIT 10
    ");
    $like = '%' . $q . '%';
    $stmt->bindValue(':q', $like, \PDO::PARAM_STR);
    $stmt->execute();
    echo json_encode($stmt->fetchAll(\PDO::FETCH_ASSOC));
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
