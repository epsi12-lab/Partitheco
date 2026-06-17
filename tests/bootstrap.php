<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
ob_start();

$sessionPath = sys_get_temp_dir() . '/partitheco-test-sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);

function assertTrue(bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function assertFalse(bool $condition, string $message): void {
    assertTrue(!$condition, $message);
}

function assertSame(mixed $expected, mixed $actual, string $message): void {
    if ($expected !== $actual) {
        throw new RuntimeException($message . ' Expected: ' . var_export($expected, true) . ' Actual: ' . var_export($actual, true));
    }
}

function assertNotEmptyValue(mixed $value, string $message): void {
    if (empty($value)) {
        throw new RuntimeException($message);
    }
}

/**
 * Connexion PostgreSQL partagée pour les tests, réutilisée d'un test à l'autre.
 * Les identifiants pointent vers une base de test jetable (docker-compose en local, service CI en pipeline).
 */
function getTestPdo(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $host = $_ENV['TEST_DB_HOST'] ?? getenv('TEST_DB_HOST') ?: 'localhost';
        $port = $_ENV['TEST_DB_PORT'] ?? getenv('TEST_DB_PORT') ?: '5432';
        $name = $_ENV['TEST_DB_NAME'] ?? getenv('TEST_DB_NAME') ?: 'partitheco_test';
        $user = $_ENV['TEST_DB_USER'] ?? getenv('TEST_DB_USER') ?: 'postgres';
        $pass = $_ENV['TEST_DB_PASS'] ?? getenv('TEST_DB_PASS') ?: 'postgres';

        $pdo = new PDO("pgsql:host={$host};port={$port};dbname={$name}", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $pdo;
}

/**
 * Repart d'un schéma vide avant chaque suite de tests, pour reproduire l'isolation
 * qu'offrait auparavant une base SQLite en mémoire par test (les ID auto-incrémentés
 * repartent de 1, comme attendu par les assertions des tests).
 */
function createTestPdo(): PDO {
    $pdo = getTestPdo();
    $pdo->exec('DROP SCHEMA IF EXISTS public CASCADE');
    $pdo->exec('CREATE SCHEMA public');
    return $pdo;
}

function createCoreSchema(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE users (
            id SERIAL PRIMARY KEY,
            username TEXT NOT NULL UNIQUE,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            first_name TEXT NULL,
            last_name TEXT NULL,
            paroisse TEXT NULL,
            role_choral TEXT NULL
        )
    ");

    $pdo->exec("
        CREATE TABLE projects (
            id SERIAL PRIMARY KEY,
            user_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            description TEXT NOT NULL,
            thumbnail TEXT NULL,
            media TEXT NULL,
            author TEXT NULL,
            arranger TEXT NULL,
            genre TEXT NULL,
            tonality TEXT NULL,
            moment_messe TEXT NULL,
            temps_liturgique TEXT NULL,
            is_liturgical INTEGER DEFAULT 0,
            voix TEXT NULL,
            date_publication TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
}

function createProjectInteractionSchema(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE comments (
            id SERIAL PRIMARY KEY,
            project_id INTEGER NOT NULL,
            author TEXT NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE favorites (
            user_id INTEGER NOT NULL,
            project_id INTEGER NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(user_id, project_id)
        )
    ");

    $pdo->exec("
        CREATE TABLE ratings (
            id SERIAL PRIMARY KEY,
            user_id INTEGER NOT NULL,
            project_id INTEGER NOT NULL,
            score INTEGER NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(user_id, project_id)
        )
    ");

    $pdo->exec("
        CREATE TABLE downloads (
            id SERIAL PRIMARY KEY,
            project_id INTEGER NOT NULL,
            user_id INTEGER NULL,
            ip_address TEXT NOT NULL,
            file_type TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE playlists (
            id SERIAL PRIMARY KEY,
            user_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            description TEXT NULL,
            event_date TEXT NULL,
            share_token TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE playlist_items (
            id SERIAL PRIMARY KEY,
            playlist_id INTEGER NOT NULL,
            project_id INTEGER NOT NULL,
            note TEXT NULL,
            position INTEGER DEFAULT 0
        )
    ");
}
