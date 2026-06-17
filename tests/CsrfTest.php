<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

return function (): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
    }

    session_id('csrf-test');
    session_start();

    $token = generate_csrf_token();
    assertNotEmptyValue($token, 'Un jeton CSRF doit etre genere');
    assertSame($token, generate_csrf_token(), 'Le jeton CSRF doit rester stable au sein de la session');

    assertFalse(verify_csrf_token(null), 'Un jeton absent doit etre rejete');
    assertFalse(verify_csrf_token(''), 'Un jeton vide doit etre rejete');
    assertFalse(verify_csrf_token('jeton-invalide'), 'Un jeton invalide doit etre rejete');
    assertTrue(verify_csrf_token($token), 'Le jeton valide doit etre accepte');

    session_unset();
    session_destroy();
};
