<?php
// api/projects/search.php
require_once __DIR__ . '/../../../bootstrap.php';

use App\Database;
use App\ProjectRepository;

try {
    $db      = new Database();
    $pdo     = $db->getPDO();
    $projectRepository = new ProjectRepository($pdo);

    $q = trim($_GET['q'] ?? '');
    $moment = trim($_GET['moment'] ?? '') ?: null;
    $temps = trim($_GET['temps'] ?? '') ?: null;
    $voix = trim($_GET['voix'] ?? '') ?: null;

    if ($q === '' && $moment === null && $temps === null && $voix === null) {
        $results = $projectRepository->getProjects(1000, 0);
    } else {
        $results = $projectRepository->search($q, $moment, $temps, $voix);
    }

    json_response($results);
} catch (\Throwable $e) {
    json_error('Erreur interne', 500);
}
