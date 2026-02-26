<?php
// bootstrap.php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/functions.php';

use Dotenv\Dotenv;

// Charger les variables d'environnement
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Définir des constantes globales si nécessaire
if (!defined('BASE_URL')) {
    define('BASE_URL', $_ENV['BASE_URL'] ?? 'http://localhost');
}
