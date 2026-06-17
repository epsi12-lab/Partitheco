<?php

declare(strict_types=1);

use App\DownloadRepository;
use App\PlaylistRepository;
use App\PlaylistSharingService;
use App\RatingRepository;

return function (): void {
    $pdo = createTestPdo();
    createCoreSchema($pdo);

    $pdo->exec("
        CREATE TABLE ratings (
            id SERIAL PRIMARY KEY,
            user_id INTEGER NOT NULL,
            project_id INTEGER NOT NULL,
            score INTEGER NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(user_id, project_id)
        )
    ");

    $pdo->exec("
        CREATE TABLE downloads (
            id SERIAL PRIMARY KEY,
            project_id INTEGER NOT NULL,
            user_id INTEGER NULL,
            ip_address TEXT NOT NULL,
            file_type TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE playlists (
            id SERIAL PRIMARY KEY,
            user_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            description TEXT NULL,
            event_date TEXT NULL,
            share_token TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE playlist_items (
            id SERIAL PRIMARY KEY,
            playlist_id INTEGER NOT NULL,
            project_id INTEGER NOT NULL,
            note TEXT NULL,
            position INTEGER DEFAULT 0
        )
    ");

    $pdo->exec("INSERT INTO users (username, email, password, first_name, last_name) VALUES ('chef', 'chef@example.test', 'x', 'Chef', 'Dechoeur')");
    $pdo->exec("INSERT INTO users (username, email, password, first_name, last_name) VALUES ('publieur', 'publieur@example.test', 'x', 'Paul', 'Auteur')");
    $pdo->exec("
        INSERT INTO projects (user_id, title, description, author, moment_messe, temps_liturgique)
        VALUES (2, 'Kyrie des anges', 'Partition test', 'Traditionnel', 'Kyrie', 'Ordinaire')
    ");

    $ratings = new RatingRepository($pdo);
    assertTrue($ratings->rate(1, 1, 4), 'La premiere note doit etre enregistree');
    assertTrue($ratings->rate(1, 1, 5), 'La mise a jour de note doit etre enregistree');
    assertSame(5, $ratings->getUserRating(1, 1), 'La note utilisateur doit etre mise a jour');
    assertSame(5.0, $ratings->getAverageRating(1), 'La moyenne doit refleter la note courante');
    assertSame(1, $ratings->getRatingCount(1), 'Le compteur de notes doit rester a 1');

    $downloads = new DownloadRepository($pdo);
    assertTrue($downloads->record(1, 1, '127.0.0.1', 'pdf'), 'Le telechargement PDF doit etre enregistre');
    assertTrue($downloads->record(1, null, '127.0.0.2', 'audio'), 'Le telechargement audio anonyme doit etre enregistre');
    assertSame(2, $downloads->getCountByProject(1), 'Le compteur de telechargements doit etre correct');
    assertSame(2, $downloads->getTotalDownloads(), 'Le total de telechargements doit etre correct');
    assertSame(2, count($downloads->getStatsByProject(1)), 'Les stats par type doivent contenir deux entrees');
    assertSame(1, count($downloads->getTopDownloaded(5)), 'Le top des telechargements doit contenir le projet');
    assertSame(2, count($downloads->getRecentDownloads(5)), 'Les telechargements recents doivent etre retournes');

    $playlists = new PlaylistRepository($pdo);
    $playlistId = $playlists->create(1, 'Messe du dimanche', 'Selection principale', '2026-03-22');
    assertTrue($playlistId > 0, 'La playlist doit etre creee');
    assertTrue($playlists->addItem($playlistId, 1, 'Commencer doucement', 1), 'L ajout d item doit reussir');
    $shareToken = $playlists->setShareToken($playlistId, 1);
    assertNotEmptyValue($shareToken, 'Le token de partage doit etre genere');

    $sharing = new PlaylistSharingService($pdo);
    $shared = $sharing->getSharedPlaylistByToken($shareToken);
    assertSame('Messe du dimanche', $shared['playlist']['name'], 'La playlist partagee doit etre retrouvee');
    assertSame(1, count($shared['items']), 'Les items partages doivent etre recuperes');

    $exportData = $sharing->getExportData($playlistId, 1, null);
    assertSame($playlistId, (int) $exportData['playlist']['id'], 'L export proprietaire doit etre autorise');

    $json = $sharing->buildJsonExport($exportData['playlist'], $exportData['items']);
    assertTrue(str_contains($json, 'Kyrie des anges'), 'Le JSON export doit contenir le titre');

    $csv = $sharing->buildCsvExport($exportData['items']);
    assertTrue(str_contains($csv, 'Titre,Auteur'), 'Le CSV export doit contenir l entete');

    $text = $sharing->buildTextExport($exportData['playlist'], $exportData['items']);
    assertTrue(str_contains($text, 'Liste des chants'), 'Le texte export doit contenir la section attendue');

    assertSame('messe-du-dimanche', $sharing->slugify('Messe du dimanche'), 'Le slug doit etre normalise');
};
