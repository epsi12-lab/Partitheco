<?php

declare(strict_types=1);

use App\CatalogService;
use App\FavoriteRepository;
use App\LiturgicalCalendar;

return function (): void {
    $catalog = new CatalogService();
    $moments = $catalog->getMoments();
    $temps = $catalog->getTempsLiturgiques(true);

    assertSame('entree', $moments[0]['slug'], 'Le premier moment catalogue doit etre entree');
    assertSame('avent', $temps[0]['slug'], 'Le premier temps liturgique doit etre avent');
    assertSame('Noël', LiturgicalCalendar::getSeason(new DateTime('2026-12-25')), 'Le 25 decembre doit etre en temps de Noel');

    $pdo = createTestPdo();
    createCoreSchema($pdo);
    $pdo->exec("CREATE TABLE favorites (user_id INTEGER NOT NULL, project_id INTEGER NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE(user_id, project_id))");

    $pdo->exec("INSERT INTO users (username, email, password, first_name, last_name) VALUES ('paul', 'paul@example.test', 'x', 'Paul', 'Martin')");
    $pdo->exec("INSERT INTO users (username, email, password, first_name, last_name) VALUES ('anne', 'anne@example.test', 'x', 'Anne', 'Bernard')");
    $pdo->exec("INSERT INTO projects (user_id, title, description) VALUES (2, 'Gloria', 'Partition de test')");

    $favorites = new FavoriteRepository($pdo);
    assertTrue($favorites->add(1, 1), 'L ajout en favoris doit reussir');
    assertTrue($favorites->add(1, 1), 'Le doublon favoris doit etre tolere');
    assertTrue($favorites->isFavorite(1, 1), 'Le favori doit etre retrouve');
    assertSame(1, count($favorites->getByUser(1)), 'Un seul favori doit etre retourne');
    assertTrue($favorites->remove(1, 1), 'La suppression du favori doit reussir');
    assertFalse($favorites->isFavorite(1, 1), 'Le favori supprime ne doit plus exister');
};
