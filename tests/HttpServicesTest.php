<?php

declare(strict_types=1);

use App\AuthService;
use App\DownloadRepository;
use App\DownloadService;
use App\PlaylistImportService;
use App\PlaylistRepository;
use App\ProjectRepository;
use App\RegistrationService;
use App\User;

return function (): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
    }

    $pdo = createTestPdo();
    createCoreSchema($pdo);
    createProjectInteractionSchema($pdo);

    $pdo->exec("INSERT INTO users (username, email, password, first_name, last_name) VALUES ('publisher', 'publisher@example.test', 'x', 'Paul', 'Auteur')");
    $pdo->exec("
        INSERT INTO projects (user_id, title, description, thumbnail, media, author, moment_messe, temps_liturgique)
        VALUES (1, 'Chant de sortie', 'Partition finale', 'missing.pdf', 'missing.mp3', 'Auteur Test', 'Envoi', 'Ordinaire')
    ");

    session_id('http-services-test');
    session_start();

    $registration = new RegistrationService(new User($pdo), new AuthService($pdo));
    $success = $registration->registerFromInput([
        'first_name' => 'Marie',
        'last_name' => 'Test',
        'username' => 'marie',
        'email' => 'marie@example.test',
        'password' => 'secret123',
        'password_confirm' => 'secret123',
        'paroisse' => 'Saint Paul',
        'role_choral' => 'Alto',
    ]);
    assertTrue($success['success'], 'L inscription valide doit reussir');
    assertSame('marie', $_SESSION['user']['username'], 'La session doit etre initialisee apres inscription');

    $failure = $registration->registerFromInput([
        'first_name' => '',
        'last_name' => '',
        'username' => '',
        'email' => 'adresse-invalide',
        'password' => 'x',
        'password_confirm' => 'y',
    ]);
    assertFalse($failure['success'], 'L inscription invalide doit echouer');
    assertTrue(count($failure['errors']) >= 4, 'Les erreurs d inscription doivent etre remontees');

    $playlistImport = new PlaylistImportService($pdo, new PlaylistRepository($pdo), new ProjectRepository($pdo));
    $importResult = $playlistImport->importFromJsonPayload(2, json_encode([
        'name' => 'Import auto',
        'description' => 'Depuis JSON',
        'items' => [
            ['project_id' => 1, 'note' => 'Par id'],
            ['title' => 'Chant de sortie', 'note' => 'Par titre'],
            ['title' => 'Inexistant'],
        ],
    ], JSON_UNESCAPED_UNICODE));
    assertTrue($importResult['success'], 'L import JSON valide doit reussir');
    assertSame(2, $importResult['added'], 'Deux items doivent etre importes');
    assertSame(1, count($importResult['not_found']), 'Un item doit etre introuvable');

    $invalidImport = $playlistImport->importFromJsonPayload(2, '{invalide');
    assertFalse($invalidImport['success'], 'Le JSON invalide doit echouer');
    assertSame('JSON invalide', $invalidImport['error'], 'Le message d erreur JSON doit etre explicite');

    $downloadService = new DownloadService(new DownloadRepository($pdo), new ProjectRepository($pdo));
    $missingId = $downloadService->handleDownloadRequest(0, 'pdf', 2, '127.0.0.1');
    assertFalse($missingId['success'], 'Un identifiant vide doit etre refuse');
    assertSame(400, $missingId['status'], 'Le statut 400 doit etre retourne');

    $invalidType = $downloadService->handleDownloadRequest(1, 'zip', 2, '127.0.0.1');
    assertFalse($invalidType['success'], 'Un type de telechargement invalide doit etre refuse');
    assertSame(400, $invalidType['status'], 'Le type invalide doit renvoyer 400');

    $download = $downloadService->handleDownloadRequest(1, 'pdf', 2, '127.0.0.1');
    assertTrue($download['success'], 'Une demande de telechargement valide doit etre acceptee');
    assertSame(1, $download['downloads'], 'Sans fichier local, le service doit retourner le compteur');

    $wrongType = $downloadService->handleDownloadRequest(1, 'video', 2, '127.0.0.1');
    assertFalse($wrongType['success'], 'Un type qui ne correspond pas au fichier doit etre refuse');
    assertSame(400, $wrongType['status'], 'Le type incoherent doit renvoyer 400');

    session_unset();
    session_destroy();
};
