<?php
// api/push-subscribe.php - API pour gérer les abonnements push

require_once __DIR__ . '/../bootstrap.php';

use App\Database;
use App\PushNotification;

header('Content-Type: application/json');

$db = new Database();
$pushObj = new PushNotification($db->getPDO());

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Action requise']);
    exit;
}

$userId = $_SESSION['user']['id'] ?? null;

switch ($input['action']) {
    case 'subscribe':
        if (!isset($input['subscription']['endpoint'], $input['subscription']['keys']['p256dh'], $input['subscription']['keys']['auth'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Données de souscription incomplètes']);
            exit;
        }
        
        $result = $pushObj->subscribe(
            $userId,
            $input['subscription']['endpoint'],
            $input['subscription']['keys']['p256dh'],
            $input['subscription']['keys']['auth']
        );
        
        echo json_encode(['success' => $result, 'message' => 'Abonnement enregistré']);
        break;
        
    case 'unsubscribe':
        if (!isset($input['endpoint'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Endpoint requis']);
            exit;
        }
        
        $result = $pushObj->unsubscribe($input['endpoint']);
        echo json_encode(['success' => $result, 'message' => 'Désabonnement effectué']);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Action inconnue']);
}
