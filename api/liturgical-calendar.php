<?php
// api/liturgical-calendar.php - API Calendrier Liturgique

require_once __DIR__ . '/../bootstrap.php';

use App\LiturgicalCalendar;

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'season';
$year = (int)($_GET['year'] ?? date('Y'));
$month = (int)($_GET['month'] ?? date('n'));
$date = $_GET['date'] ?? date('Y-m-d');

switch ($action) {
    case 'season':
        // Temps liturgique pour une date
        $dt = new DateTime($date);
        echo json_encode([
            'date' => $date,
            'season' => LiturgicalCalendar::getSeason($dt),
            'color' => LiturgicalCalendar::getSeasonColor(LiturgicalCalendar::getSeason($dt)),
            'feast' => LiturgicalCalendar::getFeastOfDay($dt)
        ]);
        break;
        
    case 'month':
        // Données pour un mois complet
        echo json_encode([
            'year' => $year,
            'month' => $month,
            'days' => LiturgicalCalendar::getMonthData($year, $month),
            'feasts' => array_filter(
                LiturgicalCalendar::getAllFeasts($year),
                fn($k) => substr($k, 5, 2) == str_pad($month, 2, '0', STR_PAD_LEFT),
                ARRAY_FILTER_USE_KEY
            )
        ]);
        break;
        
    case 'feasts':
        // Toutes les fêtes d'une année
        echo json_encode([
            'year' => $year,
            'fixed' => LiturgicalCalendar::getFixedFeasts($year),
            'mobile' => LiturgicalCalendar::getMobileFeasts($year)
        ]);
        break;
        
    case 'easter':
        // Date de Pâques
        $easter = LiturgicalCalendar::easterDate($year);
        echo json_encode([
            'year' => $year,
            'easter' => $easter->format('Y-m-d'),
            'formatted' => $easter->format('d/m/Y')
        ]);
        break;
        
    case 'next-sunday':
        // Prochain dimanche
        $today = new DateTime();
        $nextSunday = clone $today;
        if ($today->format('N') != 7) {
            $nextSunday->modify('next sunday');
        }
        echo json_encode([
            'date' => $nextSunday->format('Y-m-d'),
            'formatted' => $nextSunday->format('d/m/Y'),
            'season' => LiturgicalCalendar::getSeason($nextSunday),
            'color' => LiturgicalCalendar::getSeasonColor(LiturgicalCalendar::getSeason($nextSunday)),
            'feast' => LiturgicalCalendar::getFeastOfDay($nextSunday)
        ]);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Action inconnue. Actions disponibles: season, month, feasts, easter, next-sunday']);
}
