<?php
// api/liturgical-calendar.php - API Calendrier Liturgique

require_once __DIR__ . '/../bootstrap.php';

use App\LiturgicalCalendar;

$action = $_GET['action'] ?? 'season';
$year = (int)($_GET['year'] ?? date('Y'));
$month = (int)($_GET['month'] ?? date('n'));
$date = $_GET['date'] ?? date('Y-m-d');

switch ($action) {
    case 'season':
        // Temps liturgique pour une date
        $dt = new DateTime($date);
        json_response([
            'date' => $date,
            'season' => LiturgicalCalendar::getSeason($dt),
            'color' => LiturgicalCalendar::getSeasonColor(LiturgicalCalendar::getSeason($dt)),
            'feast' => LiturgicalCalendar::getFeastOfDay($dt)
        ]);
        
    case 'month':
        // Données pour un mois complet
        json_response([
            'year' => $year,
            'month' => $month,
            'days' => LiturgicalCalendar::getMonthData($year, $month),
            'feasts' => array_filter(
                LiturgicalCalendar::getAllFeasts($year),
                fn($k) => substr($k, 5, 2) == str_pad($month, 2, '0', STR_PAD_LEFT),
                ARRAY_FILTER_USE_KEY
            )
        ]);
        
    case 'feasts':
        // Toutes les fêtes d'une année
        json_response([
            'year' => $year,
            'fixed' => LiturgicalCalendar::getFixedFeasts($year),
            'mobile' => LiturgicalCalendar::getMobileFeasts($year)
        ]);
        
    case 'easter':
        // Date de Pâques
        $easter = LiturgicalCalendar::easterDate($year);
        json_response([
            'year' => $year,
            'easter' => $easter->format('Y-m-d'),
            'formatted' => $easter->format('d/m/Y')
        ]);
        
    case 'next-sunday':
        // Prochain dimanche
        $today = new DateTime();
        $nextSunday = clone $today;
        if ($today->format('N') != 7) {
            $nextSunday->modify('next sunday');
        }
        json_response([
            'date' => $nextSunday->format('Y-m-d'),
            'formatted' => $nextSunday->format('d/m/Y'),
            'season' => LiturgicalCalendar::getSeason($nextSunday),
            'color' => LiturgicalCalendar::getSeasonColor(LiturgicalCalendar::getSeason($nextSunday)),
            'feast' => LiturgicalCalendar::getFeastOfDay($nextSunday)
        ]);
        
    default:
        json_error('Action inconnue. Actions disponibles: season, month, feasts, easter, next-sunday', 400);
}
