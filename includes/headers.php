<?php
// includes/headers.php

function apply_security_headers(): void {
    if (PHP_SAPI === 'cli' || headers_sent()) {
        return;
    }

    $csp = $_ENV['CONTENT_SECURITY_POLICY'] ?? implode('; ', [
        "default-src 'self'",
        "img-src 'self' data: https:",
        "media-src 'self' https:",
        "frame-src 'self' https:",
        "script-src 'self' 'unsafe-inline'",
        "style-src 'self' 'unsafe-inline' https:",
        "font-src 'self' data: https:",
        "connect-src 'self' https:",
        "worker-src 'self' blob:",
        "manifest-src 'self'",
        "object-src 'none'",
        "frame-ancestors 'self'",
        "base-uri 'self'",
        "form-action 'self' https://formspree.io",
    ]);

    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header('Content-Security-Policy: ' . $csp);

    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}
