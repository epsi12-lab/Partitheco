<?php
// api/projects/autocomplete.php
require_once __DIR__ . '/../../../bootstrap.php';

use App\Database;

try {
    $q = trim($_GET['q'] ?? '');
    if (strlen($q) < 2) {
        json_response([]);
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
    json_response($stmt->fetchAll(\PDO::FETCH_ASSOC));
} catch (\Throwable $e) {
    json_error('Erreur interne', 500);
}
