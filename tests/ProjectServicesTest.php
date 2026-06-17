<?php

declare(strict_types=1);

use App\MediaUploader;
use App\ProjectFormService;
use App\ProjectPageService;
use App\ProjectRepository;

return function (): void {
    $pdo = createTestPdo();
    createCoreSchema($pdo);
    createProjectInteractionSchema($pdo);

    $pdo->exec("INSERT INTO users (username, email, password, first_name, last_name) VALUES ('alice', 'alice@example.test', 'x', 'Alice', 'Martin')");
    $pdo->exec("INSERT INTO users (username, email, password, first_name, last_name) VALUES ('bob', 'bob@example.test', 'x', 'Bob', 'Durand')");
    $pdo->exec("INSERT INTO playlists (user_id, name, description) VALUES (1, 'Messe semaine', 'Playlist test')");

    $fakeUploader = new class extends MediaUploader {
        public array $responses = [];

        public function __construct() {}

        public function uploadValidated(array $file, array $allowedMimes, string $type, bool $required = false): array {
            return $this->responses[$type] ?? [
                'success' => !$required,
                'path' => null,
                'error' => $required ? 'Le fichier principal est requis.' : null,
            ];
        }
    };

    $projectRepository = new ProjectRepository($pdo);
    $formService = new ProjectFormService($projectRepository, $fakeUploader);

    $fakeUploader->responses = [
        'image' => ['success' => true, 'path' => 'cover.pdf', 'error' => null],
        'media' => ['success' => true, 'path' => 'audio.mp3', 'error' => null],
    ];

    $prepared = $formService->prepareFromRequest([
        'title' => ' Kyrie de test ',
        'description' => ' Description liturgique ',
        'author' => 'Tradition',
        'moment_messe' => 'Kyrie',
        'temps_liturgique' => 'Ordinaire',
        'voix' => 'satb',
        'is_liturgical' => '1',
    ], [
        'file' => ['tmp_name' => '/tmp/fake-image', 'error' => UPLOAD_ERR_OK],
        'media' => ['tmp_name' => '/tmp/fake-media', 'error' => UPLOAD_ERR_OK],
    ]);

    assertSame([], $prepared['errors'], 'La preparation du formulaire valide ne doit pas retourner d erreur');
    assertSame('Kyrie de test', $prepared['data']['title'], 'Le titre doit etre nettoye');
    assertSame('cover.pdf', $prepared['data']['thumbnail'], 'Le fichier principal doit etre renseigne');
    assertSame('audio.mp3', $prepared['data']['media'], 'Le media doit etre renseigne');
    assertTrue($formService->create(1, $prepared['data']), 'La creation projet doit reussir');

    $project = $projectRepository->getById(1);
    assertSame('Kyrie de test', $project['title'], 'Le projet cree doit etre retrouve');
    assertSame('satb', $project['voix'], 'La voix doit etre persistée');

    $fakeUploader->responses = [
        'image' => ['success' => false, 'path' => null, 'error' => 'Le fichier principal est requis.'],
        'media' => ['success' => true, 'path' => null, 'error' => null],
    ];

    $invalid = $formService->prepareFromRequest([
        'title' => '',
        'description' => '',
    ], [
        'file' => [],
        'media' => [],
    ]);
    assertTrue(count($invalid['errors']) >= 2, 'Le formulaire invalide doit retourner plusieurs erreurs');

    $updatedData = $prepared['data'];
    $updatedData['title'] = 'Kyrie mis a jour';
    $updatedData['description'] = 'Nouvelle description';
    assertTrue($formService->update(1, 1, $updatedData), 'La mise a jour projet doit reussir');
    assertSame('Kyrie mis a jour', $projectRepository->getById(1)['title'], 'Le projet doit etre mis a jour');

    $pageService = new ProjectPageService($pdo);
    assertTrue($pageService->addComment(1, 'Claire', 'Tres beau chant'), 'L ajout de commentaire doit reussir');
    assertFalse($pageService->addComment(1, ' ', ' '), 'Le commentaire vide doit etre refuse');

    $pageService->toggleFavorite(1, ['id' => 1], false);
    $pageData = $pageService->buildPageData(1, ['id' => 1]);
    assertTrue($pageData['isFavorite'], 'Le projet doit etre marque en favori');
    assertSame(1, count($pageData['comments']), 'Le commentaire doit etre visible dans la page');
    assertSame(1, count($pageData['userPlaylists']), 'Les playlists utilisateur doivent etre chargees');
    assertSame(null, $pageData['avgRating'], 'Aucune note ne doit encore etre presente');

    $pageService->toggleFavorite(1, ['id' => 1], true);
    $updatedPageData = $pageService->buildPageData(1, ['id' => 1]);
    assertFalse($updatedPageData['isFavorite'], 'Le favori doit pouvoir etre retire');
};
