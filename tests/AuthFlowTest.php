<?php

declare(strict_types=1);

use App\AuthService;
use App\User;

return function (): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
    }

    $pdo = createSqliteMemoryPdo();
    createCoreSchema($pdo);

    $user = new User($pdo);
    assertTrue(
        $user->register('marie', 'marie@example.test', 'secret123', 'Marie', 'Durand'),
        'L inscription utilisateur doit reussir'
    );

    $loggedUser = $user->login('marie', 'secret123');
    assertTrue(is_array($loggedUser), 'Le login doit retourner un utilisateur');
    assertSame('marie', $loggedUser['username'], 'Le username doit correspondre');

    $rememberToken = $user->createRememberToken((int) $loggedUser['id']);
    assertNotEmptyValue($rememberToken, 'Le token remember me doit etre genere');

    $fromToken = $user->findByRememberToken($rememberToken);
    assertTrue(is_array($fromToken), 'La lecture du token remember me doit retrouver l utilisateur');
    assertSame('marie', $fromToken['username'], 'Le token remember me doit pointer vers le bon utilisateur');

    session_id('auth-flow-test');
    session_start();
    $authService = new AuthService($pdo);
    $authService->startAuthenticatedSession($fromToken);
    assertSame('marie', $_SESSION['user']['username'], 'La session auth doit etre initialisee');

    assertTrue($authService->revokeRememberToken($rememberToken), 'La revocation du token doit reussir');
    assertFalse((bool) $user->findByRememberToken($rememberToken), 'Le token revoque ne doit plus etre valide');

    session_unset();
    session_destroy();
};
