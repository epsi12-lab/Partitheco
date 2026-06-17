<?php
// classes/LiturgicalCalendar.php - API Calendrier Liturgique

declare(strict_types=1);

namespace App;

class LiturgicalCalendar {
    
    // Calcul de la date de Pâques (algorithme de Meeus/Jones/Butcher)
    public static function easterDate(int $year): \DateTime {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;
        
        return new \DateTime("$year-$month-$day");
    }
    
    // Déterminer le temps liturgique pour une date donnée
    public static function getSeason(\DateTime $date): string {
        $year = (int)$date->format('Y');
        $easter = self::easterDate($year);
        
        // Dates clés
        $ashWednesday = (clone $easter)->modify('-46 days'); // Mercredi des Cendres
        $pentecost = (clone $easter)->modify('+49 days'); // Pentecôte
        
        // Avent : 4 dimanches avant Noël (commence entre 27 nov et 3 déc)
        $christmas = new \DateTime("$year-12-25");
        $adventStart = clone $christmas;
        $adventStart->modify('last sunday')->modify('-3 weeks');
        
        // Noël de l'année précédente pour janvier
        $prevChristmas = new \DateTime(($year - 1) . "-12-25");
        $epiphany = new \DateTime("$year-01-06");
        $baptismOfLord = (clone $epiphany)->modify('next sunday');
        if ($epiphany->format('N') == 7) {
            $baptismOfLord = (clone $epiphany)->modify('+1 day');
        }
        
        // Détermination du temps
        $dayOfYear = (int)$date->format('z');
        
        // Temps de Noël (25 déc - Baptême du Seigneur)
        if ($date >= $prevChristmas && $date < new \DateTime("$year-01-01")) {
            return 'Noël';
        }
        if ($date >= new \DateTime("$year-01-01") && $date <= $baptismOfLord) {
            return 'Noël';
        }
        
        // Carême (Mercredi des Cendres - Jeudi Saint)
        $holyThursday = (clone $easter)->modify('-3 days');
        if ($date >= $ashWednesday && $date < $holyThursday) {
            return 'Carême';
        }
        
        // Triduum Pascal et Temps Pascal (Jeudi Saint - Pentecôte)
        if ($date >= $holyThursday && $date <= $pentecost) {
            return 'Pâques';
        }
        
        // Avent
        if ($date >= $adventStart && $date < $christmas) {
            return 'Avent';
        }
        
        // Noël (25 déc - fin d'année)
        if ($date >= $christmas) {
            return 'Noël';
        }
        
        // Temps Ordinaire (le reste)
        return 'Ordinaire';
    }
    
    // Obtenir le temps liturgique du prochain dimanche
    public static function nextSundaySeason(): string {
        $today = new \DateTime();
        $nextSunday = clone $today;
        
        if ($today->format('N') != 7) {
            $nextSunday->modify('next sunday');
        }
        
        return self::getSeason($nextSunday);
    }
    
    // Obtenir les informations liturgiques pour un mois
    public static function getMonthData(int $year, int $month): array {
        $data = [];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = new \DateTime("$year-$month-$day");
            $data[$day] = [
                'date' => $date->format('Y-m-d'),
                'dayOfWeek' => (int)$date->format('N'),
                'isSunday' => $date->format('N') == 7,
                'season' => self::getSeason($date),
                'seasonColor' => self::getSeasonColor(self::getSeason($date))
            ];
        }
        
        return $data;
    }
    
    // Couleur liturgique par temps
    public static function getSeasonColor(string $season): string {
        return match($season) {
            'Avent' => '#7b1fa2',
            'Noël' => '#c62828',
            'Carême' => '#5d4037',
            'Pâques' => '#ffc107',
            'Ordinaire' => '#388e3c',
            default => '#607d8b'
        };
    }
    
    // Fêtes fixes importantes
    public static function getFixedFeasts(int $year): array {
        return [
            "$year-01-01" => ['name' => 'Sainte Marie, Mère de Dieu', 'rank' => 'solennité'],
            "$year-01-06" => ['name' => 'Épiphanie du Seigneur', 'rank' => 'solennité'],
            "$year-02-02" => ['name' => 'Présentation du Seigneur', 'rank' => 'fête'],
            "$year-03-19" => ['name' => 'Saint Joseph', 'rank' => 'solennité'],
            "$year-03-25" => ['name' => 'Annonciation du Seigneur', 'rank' => 'solennité'],
            "$year-06-24" => ['name' => 'Nativité de Saint Jean-Baptiste', 'rank' => 'solennité'],
            "$year-06-29" => ['name' => 'Saints Pierre et Paul', 'rank' => 'solennité'],
            "$year-08-15" => ['name' => 'Assomption de la Vierge Marie', 'rank' => 'solennité'],
            "$year-11-01" => ['name' => 'Toussaint', 'rank' => 'solennité'],
            "$year-11-02" => ['name' => 'Commémoration des fidèles défunts', 'rank' => 'commémoration'],
            "$year-12-08" => ['name' => 'Immaculée Conception', 'rank' => 'solennité'],
            "$year-12-25" => ['name' => 'Nativité du Seigneur', 'rank' => 'solennité'],
        ];
    }
    
    // Fêtes mobiles (basées sur Pâques)
    public static function getMobileFeasts(int $year): array {
        $easter = self::easterDate($year);
        
        return [
            (clone $easter)->modify('-46 days')->format('Y-m-d') => ['name' => 'Mercredi des Cendres', 'rank' => 'jour'],
            (clone $easter)->modify('-7 days')->format('Y-m-d') => ['name' => 'Dimanche des Rameaux', 'rank' => 'dimanche'],
            (clone $easter)->modify('-3 days')->format('Y-m-d') => ['name' => 'Jeudi Saint', 'rank' => 'triduum'],
            (clone $easter)->modify('-2 days')->format('Y-m-d') => ['name' => 'Vendredi Saint', 'rank' => 'triduum'],
            (clone $easter)->modify('-1 day')->format('Y-m-d') => ['name' => 'Samedi Saint', 'rank' => 'triduum'],
            $easter->format('Y-m-d') => ['name' => 'Pâques', 'rank' => 'solennité'],
            (clone $easter)->modify('+39 days')->format('Y-m-d') => ['name' => 'Ascension', 'rank' => 'solennité'],
            (clone $easter)->modify('+49 days')->format('Y-m-d') => ['name' => 'Pentecôte', 'rank' => 'solennité'],
            (clone $easter)->modify('+56 days')->format('Y-m-d') => ['name' => 'Sainte Trinité', 'rank' => 'solennité'],
            (clone $easter)->modify('+60 days')->format('Y-m-d') => ['name' => 'Saint-Sacrement', 'rank' => 'solennité'],
        ];
    }
    
    // Obtenir toutes les fêtes d'une année
    public static function getAllFeasts(int $year): array {
        return array_merge(self::getFixedFeasts($year), self::getMobileFeasts($year));
    }
    
    // Obtenir la fête du jour (si existe)
    public static function getFeastOfDay(\DateTime $date): ?array {
        $year = (int)$date->format('Y');
        $feasts = self::getAllFeasts($year);
        $dateStr = $date->format('Y-m-d');
        
        return $feasts[$dateStr] ?? null;
    }
}
