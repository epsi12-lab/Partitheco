<?php
// includes/security.php

function require_post_method(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect_error('405', 'Methode non autorisee.');
    }
}

function require_post_method_api(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_error('Methode non autorisee', 405);
    }
}

function verify_csrf_or_fail(?string $token): void {
    if (!verify_csrf_token($token)) {
        redirect_error('403', 'Action non autorisee (CSRF).');
    }
}

function verify_api_csrf_or_fail(?string $token): void {
    if (!verify_csrf_token($token)) {
        json_error('Jeton CSRF invalide', 403);
    }
}
