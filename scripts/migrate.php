<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

require_once __DIR__ . '/../bootstrap.php';
use App\Database;

$db = new Database();
$db->initTables();

echo "Migration terminée avec succès.\n";
