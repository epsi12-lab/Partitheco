<?php
// classes/Database.php

namespace App;

use PDO;
use PDOException;

class Database {
    private PDO $pdo;

    public function __construct(string $dbPath = __DIR__ . '/../db.sqlite') {
        if (isset($_ENV['DB_HOST']) && !empty($_ENV['DB_HOST'])) {
            $dsn = "pgsql:host=" . $_ENV['DB_HOST'] . ";port=" . ($_ENV['DB_PORT'] ?? 5432) . ";dbname=" . $_ENV['DB_NAME'];
            $this->pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);
        } else {
            $this->pdo = new PDO('sqlite:' . $dbPath);
        }
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Initialise les tables de la base de données.
     * Utile lors de l'installation ou de la migration.
     */
    public function initTables(): void {
        try {
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id       INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT    NOT NULL UNIQUE,
                    email    TEXT    NOT NULL UNIQUE,
                    password TEXT    NOT NULL,
                    paroisse TEXT,
                    role_choral TEXT
                )
            ");

            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS projects (
                    id               INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id          INTEGER NOT NULL,
                    title            TEXT    NOT NULL,
                    description      TEXT    NOT NULL,
                    thumbnail        TEXT,
                    media            TEXT,
                    author           TEXT,
                    arranger         TEXT,
                    genre            TEXT,
                    tonality         TEXT,
                    moment_messe     TEXT,
                    temps_liturgique TEXT,
                    is_liturgical    BOOLEAN DEFAULT 0,
                    voix             TEXT,
                    date_publication DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY(user_id) REFERENCES users(id)
                )
            ");

            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS comments (
                    id          INTEGER PRIMARY KEY AUTOINCREMENT,
                    project_id  INTEGER NOT NULL,
                    author      TEXT NOT NULL,
                    content     TEXT NOT NULL,
                    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY(project_id) REFERENCES projects(id)
                )
            ");

            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS subscribers (
                    id    INTEGER PRIMARY KEY AUTOINCREMENT,
                    email TEXT NOT NULL UNIQUE,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");

            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS favorites (
                    id         INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id    INTEGER NOT NULL,
                    project_id INTEGER NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY(user_id)    REFERENCES users(id),
                    FOREIGN KEY(project_id) REFERENCES projects(id),
                    UNIQUE(user_id, project_id)
                )
            ");

            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS playlists (
                    id          INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id     INTEGER NOT NULL,
                    name        TEXT NOT NULL,
                    description TEXT,
                    event_date  DATE,
                    share_token TEXT,
                    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY(user_id) REFERENCES users(id)
                )
            ");

            // Migration: ajouter share_token si manquant
            $existingPlaylists = $this->pdo
                ->query("PRAGMA table_info(playlists)")
                ->fetchAll(PDO::FETCH_COLUMN, 1);
            if (!in_array('share_token', $existingPlaylists, true)) {
                $this->pdo->exec("ALTER TABLE playlists ADD COLUMN share_token TEXT");
            }

            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS playlist_items (
                    id          INTEGER PRIMARY KEY AUTOINCREMENT,
                    playlist_id INTEGER NOT NULL,
                    project_id  INTEGER NOT NULL,
                    note        TEXT,
                    position    INTEGER DEFAULT 0,
                    FOREIGN KEY(playlist_id) REFERENCES playlists(id),
                    FOREIGN KEY(project_id)  REFERENCES projects(id)
                )
            ");

            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS downloads (
                    id          INTEGER PRIMARY KEY AUTOINCREMENT,
                    project_id  INTEGER NOT NULL,
                    user_id     INTEGER,
                    ip_address  TEXT,
                    file_type   TEXT,
                    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY(project_id) REFERENCES projects(id),
                    FOREIGN KEY(user_id)    REFERENCES users(id)
                )
            ");

            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS ratings (
                    id          INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id     INTEGER NOT NULL,
                    project_id  INTEGER NOT NULL,
                    score       INTEGER NOT NULL CHECK(score >= 1 AND score <= 5),
                    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY(user_id)    REFERENCES users(id),
                    FOREIGN KEY(project_id) REFERENCES projects(id),
                    UNIQUE(user_id, project_id)
                )
            ");

            $existingProjects = $this->pdo
                ->query("PRAGMA table_info(projects)")
                ->fetchAll(PDO::FETCH_COLUMN, 1);

            $projectCols = [
                'media', 'author', 'arranger', 'genre', 'tonality',
                'moment_messe', 'temps_liturgique', 'is_liturgical', 'voix'
            ];
            foreach ($projectCols as $col) {
                if (!in_array($col, $existingProjects, true)) {
                    $type = ($col === 'is_liturgical') ? 'BOOLEAN DEFAULT 0' : 'TEXT';
                    $this->pdo->exec("ALTER TABLE projects ADD COLUMN $col $type");
                }
            }

            $existingUsers = $this->pdo
                ->query("PRAGMA table_info(users)")
                ->fetchAll(PDO::FETCH_COLUMN, 1);
            foreach (['paroisse', 'role_choral'] as $col) {
                if (!in_array($col, $existingUsers, true)) {
                    $this->pdo->exec("ALTER TABLE users ADD COLUMN $col TEXT");
                }
            }
        } catch (PDOException $e) {
            die("Database error: " . $e->getMessage());
        }
    }

    public function getPDO(): PDO {
        return $this->pdo;
    }
}
