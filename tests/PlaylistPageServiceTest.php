<?php

declare(strict_types=1);

use App\PlaylistPageService;

return function (): void {
    $pdo = createSqliteMemoryPdo();
    createCoreSchema($pdo);
    createProjectInteractionSchema($pdo);

    $pdo->exec("INSERT INTO users (username, email, password, first_name, last_name) VALUES ('owner', 'owner@example.test', 'x', 'Owner', 'User')");
    $pdo->exec("INSERT INTO users (username, email, password, first_name, last_name) VALUES ('publisher', 'publisher@example.test', 'x', 'Pub', 'Lisher')");
    $pdo->exec("INSERT INTO projects (user_id, title, description, author, moment_messe, temps_liturgique) VALUES (2, 'Sanctus simple', 'Partition simple', 'Auteur 1', 'Sanctus', 'Ordinaire')");
    $pdo->exec("INSERT INTO projects (user_id, title, description, author, moment_messe, temps_liturgique) VALUES (2, 'Agnus Dei', 'Partition agnus', 'Auteur 2', 'Agnus Dei', 'Ordinaire')");
    $pdo->exec("INSERT INTO playlists (user_id, name, description, event_date) VALUES (1, 'Messe dominicale', 'Liste principale', '2026-03-22')");

    $service = new PlaylistPageService($pdo);

    $playlist = $service->getAccessiblePlaylist(1, 1);
    assertSame('Messe dominicale', $playlist['name'], 'La playlist du proprietaire doit etre accessible');

    assertTrue($service->addItem(1, 1, 'Ouverture'), 'L ajout du premier item doit reussir');
    assertTrue($service->addItem(1, 2, 'Finale'), 'L ajout du second item doit reussir');
    assertFalse($service->addItem(1, 0, 'Invalide'), 'Un projet invalide ne doit pas etre ajoute');

    $pageData = $service->buildPageData(1, 1);
    assertSame(2, count($pageData['items']), 'La page playlist doit charger les items');

    $shareToken = $service->generateShareToken(1, 1);
    assertNotEmptyValue($shareToken, 'Le token de partage doit etre genere');

    $firstItemId = (int) $pageData['items'][0]['item_id'];
    assertTrue($service->removeItem($firstItemId), 'La suppression d item doit reussir');
    assertFalse($service->removeItem(0), 'La suppression d item invalide doit echouer');

    $tmpFile = tempnam(sys_get_temp_dir(), 'playlist-import-');
    file_put_contents($tmpFile, json_encode([
        'items' => [
            ['project_id' => 2, 'note' => 'Ajoute par ID'],
            ['title' => 'Sanctus simple', 'note' => 'Ajoute par titre'],
            ['title' => 'Projet introuvable'],
        ],
    ], JSON_UNESCAPED_UNICODE));

    $import = $service->importFromJsonFile(1, ['tmp_name' => $tmpFile]);
    unlink($tmpFile);

    assertSame(2, $import['imported'], 'Deux projets doivent etre importes');
    assertSame(1, $import['not_found'], 'Un projet doit etre signale comme introuvable');

    $finalPageData = $service->buildPageData(1, 1);
    assertTrue(count($finalPageData['items']) >= 3, 'La playlist finale doit contenir les items restants et importes');
};
