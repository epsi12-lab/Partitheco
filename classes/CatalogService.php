<?php
// classes/CatalogService.php

declare(strict_types=1);

namespace App;

class CatalogService {
    public function getMoments(): array {
        return [
            ['slug' => 'entree', 'nom' => 'Entree', 'icon' => '🚪'],
            ['slug' => 'kyrie', 'nom' => 'Kyrie', 'icon' => '🙏'],
            ['slug' => 'gloria', 'nom' => 'Gloria', 'icon' => '✨'],
            ['slug' => 'psaume', 'nom' => 'Psaume', 'icon' => '📖'],
            ['slug' => 'acclamation', 'nom' => 'Acclamation', 'icon' => '🎵'],
            ['slug' => 'credo', 'nom' => 'Credo', 'icon' => '✝️'],
            ['slug' => 'offertoire', 'nom' => 'Offertoire', 'icon' => '🍞'],
            ['slug' => 'sanctus', 'nom' => 'Sanctus', 'icon' => '👼'],
            ['slug' => 'agnus', 'nom' => 'Agnus Dei', 'icon' => '🐑'],
            ['slug' => 'communion', 'nom' => 'Communion', 'icon' => '🍷'],
            ['slug' => 'envoi', 'nom' => 'Envoi', 'icon' => '🕊️'],
            ['slug' => 'marie', 'nom' => 'Chants a Marie', 'icon' => '💙'],
        ];
    }

    public function getPublicationMoments(): array {
        return [
            ['slug' => 'Entrée', 'nom' => 'Entrée', 'icon' => '🚪'],
            ['slug' => 'Kyrie', 'nom' => 'Kyrie', 'icon' => '🙏'],
            ['slug' => 'Gloria', 'nom' => 'Gloria', 'icon' => '✨'],
            ['slug' => 'Psaume', 'nom' => 'Psaume', 'icon' => '📖'],
            ['slug' => 'Acclamation', 'nom' => 'Acclamation', 'icon' => '🎵'],
            ['slug' => 'Credo', 'nom' => 'Credo', 'icon' => '✝️'],
            ['slug' => 'Offertoire', 'nom' => 'Offertoire', 'icon' => '🍞'],
            ['slug' => 'Sanctus', 'nom' => 'Sanctus', 'icon' => '👼'],
            ['slug' => 'Agnus Dei', 'nom' => 'Agnus Dei', 'icon' => '🐑'],
            ['slug' => 'Communion', 'nom' => 'Communion', 'icon' => '🍷'],
            ['slug' => 'Envoi', 'nom' => 'Envoi', 'icon' => '🕊️'],
            ['slug' => 'Marie', 'nom' => 'Chants à Marie', 'icon' => '💙'],
        ];
    }

    public function getTempsLiturgiques(bool $lowercaseSlugs = false): array {
        if ($lowercaseSlugs) {
            return [
                ['slug' => 'avent', 'nom' => 'Avent'],
                ['slug' => 'noel', 'nom' => 'Noël'],
                ['slug' => 'careme', 'nom' => 'Carême'],
                ['slug' => 'paques', 'nom' => 'Pâques'],
                ['slug' => 'ordinaire', 'nom' => 'Temps Ordinaire'],
            ];
        }

        return [
            ['slug' => 'Avent', 'nom' => 'Avent'],
            ['slug' => 'Noël', 'nom' => 'Noël'],
            ['slug' => 'Carême', 'nom' => 'Carême'],
            ['slug' => 'Pâques', 'nom' => 'Pâques'],
            ['slug' => 'Ordinaire', 'nom' => 'Temps Ordinaire'],
        ];
    }

    public function getVoixOptions(): array {
        return [
            ['slug' => 'unisson', 'nom' => 'Unisson'],
            ['slug' => 'satb', 'nom' => 'SATB'],
            ['slug' => 'solo', 'nom' => 'Solo'],
            ['slug' => '2voix', 'nom' => '2 voix'],
            ['slug' => '3voix', 'nom' => '3 voix'],
        ];
    }

    public function buildHomePageData(ProjectRepository $projectRepository): array {
        $projects = $projectRepository->getProjects(6, 0);
        $allProjects = $projectRepository->getProjects(100, 0);
        $chantDuJour = !empty($allProjects) ? $allProjects[array_rand($allProjects)] : null;

        try {
            $season = LiturgicalCalendar::nextSundaySeason();
            $suggestions = $projectRepository->getByTemps($season, 4);
        } catch (\Throwable $e) {
            $season = 'Ordinaire';
            $suggestions = [];
        }

        $prochainsDimanches = [];
        $today = new \DateTime();
        for ($i = 0; $i < 3; $i++) {
            $sunday = clone $today;
            $sunday->modify('next sunday');
            $sunday->modify("+{$i} week");
            $prochainsDimanches[] = [
                'date' => $sunday->format('d/m/Y'),
                'jour' => $sunday->format('l'),
                'temps' => $season,
            ];
        }

        return [
            'projects' => $projects,
            'allProjects' => $allProjects,
            'chantDuJour' => $chantDuJour,
            'season' => $season,
            'suggestions' => $suggestions,
            'prochainsDimanches' => $prochainsDimanches,
            'totalPartitions' => count($allProjects),
            'moments' => $this->getMoments(),
            'tempsLiturgiques' => $this->getTempsLiturgiques(true),
        ];
    }
}
