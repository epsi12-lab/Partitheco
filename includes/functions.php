<?php
// includes/functions.php

/**
 * Génère un jeton CSRF et le stocke en session si nécessaire.
 * @return string
 */
function generate_csrf_token(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie si le jeton CSRF fourni est valide.
 * @param string|null $token
 * @return bool
 */
function verify_csrf_token(?string $token): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Affiche un champ caché contenant le jeton CSRF.
 */
function csrf_input(): void {
    echo '<input type="hidden" name="csrf_token" value="' . generate_csrf_token() . '">';
}

/**
 * Redirige vers la page d'erreur.
 * @param string $code
 * @param string|null $message
 */
function redirect_error(string $code = '500', ?string $message = null): void {
    $url = "error.php?code=" . urlencode($code);
    if ($message) {
        $url .= "&msg=" . urlencode($message);
    }
    header("Location: $url");
    exit;
}
