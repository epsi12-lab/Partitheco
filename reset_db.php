<?php
require_once __DIR__ . '/bootstrap.php';
use App\Database;

$db = new Database();
$db->initTables();
$pdo = $db->getPDO();

// Liste des tables à vider
$tables = ['comments', 'projects', 'users', /* etc. */];

foreach ($tables as $table) {
    // vide la table
    $pdo->exec("DELETE FROM {$table}");
    // réinitialise l’autoincrement
    $pdo->exec("DELETE FROM sqlite_sequence WHERE name='{$table}'");
}

echo "Toutes les tables ont été vidées et réinitialisées.\n";
