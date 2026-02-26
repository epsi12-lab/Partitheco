<?php
// api/livrets.php - Génération de livrets mensuels PDF
require_once __DIR__ . '/../bootstrap.php';

use App\Database;
use App\Project;

header('Content-Type: application/json');

$db = new Database();
$pdo = $db->getPDO();
$projectObj = new Project($pdo);

$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$temps = $_GET['temps'] ?? null;

// Récupérer les partitions du mois
$startDate = "$year-$month-01";
$endDate = date('Y-m-t', strtotime($startDate));

$sql = "SELECT p.*, u.username FROM projects p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.date_publication BETWEEN :start AND :end";
if ($temps) {
    $sql .= " AND p.temps_liturgique = :temps";
}
$sql .= " ORDER BY p.moment_messe, p.title";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':start', $startDate);
$stmt->bindValue(':end', $endDate . ' 23:59:59');
if ($temps) {
    $stmt->bindValue(':temps', $temps);
}
$stmt->execute();
$partitions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$moisFr = [
    '01' => 'Janvier', '02' => 'Février', '03' => 'Mars', '04' => 'Avril',
    '05' => 'Mai', '06' => 'Juin', '07' => 'Juillet', '08' => 'Août',
    '09' => 'Septembre', '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre'
];

echo json_encode([
    'success' => true,
    'livret' => [
        'titre' => 'Livret ' . $moisFr[$month] . ' ' . $year,
        'mois' => $moisFr[$month],
        'annee' => $year,
        'temps_liturgique' => $temps,
        'partitions' => $partitions,
        'total' => count($partitions)
    ]
]);
