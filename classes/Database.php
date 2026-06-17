<?php
// classes/Database.php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

class Database {
    private PDO $pdo;

    public function __construct() {
        $dsn = "pgsql:host=" . $_ENV['DB_HOST'] . ";port=" . ($_ENV['DB_PORT'] ?? 5432) . ";dbname=" . $_ENV['DB_NAME'];
        $this->pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Initialise les tables de la base de données.
     * Utile lors de l'installation ou de la migration.
     */
    public function initTables(): void {
        try {
            $this->initTablesPostgres();
        } catch (PDOException $e) {
            error_log("Database init error: " . $e->getMessage());
            throw new \RuntimeException("Impossible d'initialiser la base de données.", 0, $e);
        }
    }

    /**
     * Initialise les tables pour PostgreSQL
     */
    private function initTablesPostgres(): void {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                username TEXT NOT NULL UNIQUE,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                first_name TEXT,
                last_name TEXT,
                paroisse TEXT,
                role_choral TEXT
            )
        ");

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS projects (
                id SERIAL PRIMARY KEY,
                user_id INTEGER NOT NULL REFERENCES users(id),
                title TEXT NOT NULL,
                description TEXT NOT NULL,
                thumbnail TEXT,
                media TEXT,
                author TEXT,
                arranger TEXT,
                genre TEXT,
                tonality TEXT,
                moment_messe TEXT,
                temps_liturgique TEXT,
                is_liturgical BOOLEAN DEFAULT FALSE,
                voix TEXT,
                date_publication TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS comments (
                id SERIAL PRIMARY KEY,
                project_id INTEGER NOT NULL REFERENCES projects(id),
                author TEXT NOT NULL,
                content TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS subscribers (
                id SERIAL PRIMARY KEY,
                email TEXT NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS favorites (
                id SERIAL PRIMARY KEY,
                user_id INTEGER NOT NULL REFERENCES users(id),
                project_id INTEGER NOT NULL REFERENCES projects(id),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(user_id, project_id)
            )
        ");

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS playlists (
                id SERIAL PRIMARY KEY,
                user_id INTEGER NOT NULL REFERENCES users(id),
                name TEXT NOT NULL,
                description TEXT,
                event_date DATE,
                share_token TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS playlist_items (
                id SERIAL PRIMARY KEY,
                playlist_id INTEGER NOT NULL REFERENCES playlists(id),
                project_id INTEGER NOT NULL REFERENCES projects(id),
                note TEXT,
                position INTEGER DEFAULT 0
            )
        ");

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS downloads (
                id SERIAL PRIMARY KEY,
                project_id INTEGER NOT NULL REFERENCES projects(id),
                user_id INTEGER REFERENCES users(id),
                ip_address TEXT,
                file_type TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS ratings (
                id SERIAL PRIMARY KEY,
                user_id INTEGER NOT NULL REFERENCES users(id),
                project_id INTEGER NOT NULL REFERENCES projects(id),
                score INTEGER NOT NULL CHECK(score >= 1 AND score <= 5),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(user_id, project_id)
            )
        ");
    }

    public function getPDO(): PDO {
        return $this->pdo;
    }
}
