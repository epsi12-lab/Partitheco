<?php
// api/search-lyrics.php - Recherche par paroles

require_once __DIR__ . '/../../bootstrap.php';

use App\Database;

$db = new Database();
$pdo = $db->getPDO();

$query = trim($_GET['q'] ?? '');
$limit = min((int)($_GET['limit'] ?? 20), 50);

if (strlen($query) < 3) {
    json_response(['error' => 'La recherche doit contenir au moins 3 caractères', 'results' => []], 400);
}

// Recherche dans titre, description (paroles), auteur
$sql = "
    SELECT p.id, p.title, p.author, p.description, p.moment_messe, p.temps_liturgique,
           CASE
               WHEN p.title ILIKE :exact THEN 3
               WHEN p.description ILIKE :exact THEN 2
               ELSE 1
           END as relevance
    FROM projects p
    WHERE p.title ILIKE :q
       OR p.description ILIKE :q
       OR p.author ILIKE :q
    ORDER BY relevance DESC, p.date_publication DESC
    LIMIT :limit
";

$stmt = $pdo->prepare($sql);
$like = '%' . $query . '%';
$exact = '%' . $query . '%';
$stmt->bindValue(':q', $like, PDO::PARAM_STR);
$stmt->bindValue(':exact', $exact, PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Extraire un extrait des paroles contenant le terme recherché.
// Le texte est échappé avant insertion JSON->HTML pour éviter une injection XSS
// via le titre/les paroles d'une partition ; seule la balise <mark> est volontaire.
foreach ($results as &$result) {
    $desc = $result['description'];
    $pos = stripos($desc, $query);
    if ($pos !== false) {
        $start = max(0, $pos - 50);
        $end = min(strlen($desc), $pos + strlen($query) + 50);
        $before = substr($desc, $start, $pos - $start);
        $match = substr($desc, $pos, strlen($query));
        $after = substr($desc, $pos + strlen($query), $end - ($pos + strlen($query)));

        $result['excerpt'] = ($start > 0 ? '...' : '')
            . htmlspecialchars($before, ENT_QUOTES, 'UTF-8')
            . '<mark>' . htmlspecialchars($match, ENT_QUOTES, 'UTF-8') . '</mark>'
            . htmlspecialchars($after, ENT_QUOTES, 'UTF-8')
            . ($end < strlen($desc) ? '...' : '');
    } else {
        $result['excerpt'] = htmlspecialchars(substr($desc, 0, 100), ENT_QUOTES, 'UTF-8') . (strlen($desc) > 100 ? '...' : '');
    }
    unset($result['description']); // Ne pas renvoyer la description complète
}

json_response([
    'query' => $query,
    'count' => count($results),
    'results' => $results
]);
