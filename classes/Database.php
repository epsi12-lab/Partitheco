<?php
// classes/Database.php

class Database {
    private PDO $pdo;

    public function __construct(string $dbPath = __DIR__ . '/../db.sqlite') {
        $this->pdo = new PDO('sqlite:' . $dbPath);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id       INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT    NOT NULL UNIQUE,
                    email    TEXT    NOT NULL UNIQUE,
                    password TEXT    NOT NULL
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

            $existing = $this->pdo
                ->query("PRAGMA table_info(projects)")
                ->fetchAll(PDO::FETCH_COLUMN, 1);

            foreach (['media','author','arranger','genre','tonality'] as $col) {
                if (!in_array($col, $existing, true)) {
                    $this->pdo->exec("ALTER TABLE projects ADD COLUMN $col TEXT");
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
