<?php

declare(strict_types=1);

use App\ContactFormService;

return function (): void {
    $service = new ContactFormService();
    $translations = [
        'error_name' => 'Nom requis',
        'error_name_length' => 'Nom trop long',
        'error_email' => 'Email requis',
        'error_email_invalid' => 'Email invalide',
        'error_message' => 'Message requis',
        'error_message_length' => 'Message trop long',
    ];

    $valid = $service->validate([
        'name' => ' Jeanne ',
        'email' => 'jeanne@example.test',
        'message' => 'Bonjour a tous',
    ], $translations);

    assertTrue($valid['success'], 'Le formulaire de contact valide doit reussir');
    assertSame('Jeanne', $valid['data']['name'], 'Le nom doit etre nettoye');
    assertSame([], $valid['errors'], 'Aucune erreur ne doit etre retournee');

    $invalid = $service->validate([
        'name' => '',
        'email' => 'email-invalide',
        'message' => '',
    ], $translations);

    assertFalse($invalid['success'], 'Le formulaire invalide doit echouer');
    assertSame(3, count($invalid['errors']), 'Les erreurs de validation attendues doivent etre retournees');
};
