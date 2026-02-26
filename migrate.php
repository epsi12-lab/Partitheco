<?php
require_once __DIR__ . '/bootstrap.php';
use App\Database;

$db = new Database();
$db->initTables();

echo "Migration terminée avec succès.\n";
