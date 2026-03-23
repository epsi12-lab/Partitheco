<?php
// includes/auth.php

function require_auth_redirect(string $location = 'login.php'): void {
    if (!isset($_SESSION['user'])) {
        header("Location: $location");
        exit;
    }
}

function require_auth_api(): void {
    if (!isset($_SESSION['user'])) {
        json_error('Non authentifie', 401);
    }
}
