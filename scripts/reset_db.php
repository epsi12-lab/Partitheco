<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

require_once __DIR__ . '/../bootstrap.php';
use App\Database;

$db = new Database();
$db->initTables();
$pdo = $db->getPDO();

// Liste des tables à vider
$tables = ['comments', 'projects', 'users', /* etc. */];

foreach ($tables as $table) {
    // vide la table
    $pdo->exec("DELETE FROM {$table}");
    // réinitialise la séquence d'auto-incrément (PostgreSQL)
    $pdo->exec("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), 1, false)");
}

echo "Toutes les tables ont été vidées et réinitialisées.\n";
