<?php
// api/push-subscribe.php - API pour gérer les abonnements push

session_start();
require_once __DIR__ . '/../bootstrap.php';

use App\Database;
use App\PushNotification;

$db = new Database();
$pushObj = new PushNotification($db->getPDO());

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    json_error('Action requise', 400);
}

require_post_method_api();
verify_api_csrf_or_fail(get_csrf_header_token());

$userId = $_SESSION['user']['id'] ?? null;

switch ($input['action']) {
    case 'subscribe':
        if (!isset($input['subscription']['endpoint'], $input['subscription']['keys']['p256dh'], $input['subscription']['keys']['auth'])) {
            json_error('Donnees de souscription incompletes', 400);
        }
        
        $result = $pushObj->subscribe(
            $userId,
            $input['subscription']['endpoint'],
            $input['subscription']['keys']['p256dh'],
            $input['subscription']['keys']['auth']
        );
        
        json_response(['success' => $result, 'message' => 'Abonnement enregistre']);
        break;
        
    case 'unsubscribe':
        if (!isset($input['endpoint'])) {
            json_error('Endpoint requis', 400);
        }
        
        $result = $pushObj->unsubscribe($input['endpoint']);
        json_response(['success' => $result, 'message' => 'Desabonnement effectue']);
        break;
        
    default:
        json_error('Action inconnue', 400);
}
