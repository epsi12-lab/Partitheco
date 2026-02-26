<?php
// migrate_postgres.php - Script de migration SQLite vers PostgreSQL
// Usage: php migrate_postgres.php

require_once __DIR__ . '/bootstrap.php';

echo "=== Migration SQLite vers PostgreSQL ===\n\n";

// Vérifier les variables d'environnement PostgreSQL
if (empty($_ENV['DB_HOST']) || empty($_ENV['DB_NAME']) || empty($_ENV['DB_USER'])) {
    die("Erreur: Variables d'environnement PostgreSQL non configurées.\n" .
        "Définissez DB_HOST, DB_NAME, DB_USER, DB_PASS dans .env\n");
}

// Connexion SQLite source
$sqlitePath = __DIR__ . '/db.sqlite';
if (!file_exists($sqlitePath)) {
    die("Erreur: Base SQLite introuvable: $sqlitePath\n");
}

try {
    $sqlite = new PDO('sqlite:' . $sqlitePath);
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connexion SQLite OK\n";
} catch (PDOException $e) {
    die("Erreur SQLite: " . $e->getMessage() . "\n");
}

// Connexion PostgreSQL destination
try {
    $dsn = "pgsql:host={$_ENV['DB_HOST']};port=5432;dbname={$_ENV['DB_NAME']}";
    $pgsql = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'] ?? '');
    $pgsql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connexion PostgreSQL OK\n\n";
} catch (PDOException $e) {
    die("Erreur PostgreSQL: " . $e->getMessage() . "\n");
}

// Créer les tables PostgreSQL
echo "Création des tables PostgreSQL...\n";

$pgsql->exec("
    CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        username VARCHAR(255) NOT NULL UNIQUE,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        paroisse VARCHAR(255),
        role_choral VARCHAR(255)
    )
");
echo "  ✓ Table users\n";

$pgsql->exec("
    CREATE TABLE IF NOT EXISTS projects (
        id SERIAL PRIMARY KEY,
        user_id INTEGER NOT NULL REFERENCES users(id),
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        thumbnail VARCHAR(255),
        media VARCHAR(255),
        author VARCHAR(255),
        arranger VARCHAR(255),
        genre VARCHAR(255),
        tonality VARCHAR(255),
        moment_messe VARCHAR(255),
        temps_liturgique VARCHAR(255),
        is_liturgical BOOLEAN DEFAULT FALSE,
        voix VARCHAR(255),
        date_publication TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");
echo "  ✓ Table projects\n";

$pgsql->exec("
    CREATE TABLE IF NOT EXISTS comments (
        id SERIAL PRIMARY KEY,
        project_id INTEGER NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
        author VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");
echo "  ✓ Table comments\n";

$pgsql->exec("
    CREATE TABLE IF NOT EXISTS subscribers (
        id SERIAL PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");
echo "  ✓ Table subscribers\n";

$pgsql->exec("
    CREATE TABLE IF NOT EXISTS favorites (
        id SERIAL PRIMARY KEY,
        user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        project_id INTEGER NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(user_id, project_id)
    )
");
echo "  ✓ Table favorites\n";

$pgsql->exec("
    CREATE TABLE IF NOT EXISTS playlists (
        id SERIAL PRIMARY KEY,
        user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        event_date DATE,
        share_token VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");
echo "  ✓ Table playlists\n";

$pgsql->exec("
    CREATE TABLE IF NOT EXISTS playlist_items (
        id SERIAL PRIMARY KEY,
        playlist_id INTEGER NOT NULL REFERENCES playlists(id) ON DELETE CASCADE,
        project_id INTEGER NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
        note TEXT,
        position INTEGER DEFAULT 0
    )
");
echo "  ✓ Table playlist_items\n";

$pgsql->exec("
    CREATE TABLE IF NOT EXISTS downloads (
        id SERIAL PRIMARY KEY,
        project_id INTEGER NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
        user_id INTEGER REFERENCES users(id),
        ip_address VARCHAR(45),
        file_type VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");
echo "  ✓ Table downloads\n";

$pgsql->exec("
    CREATE TABLE IF NOT EXISTS ratings (
        id SERIAL PRIMARY KEY,
        user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        project_id INTEGER NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
        score INTEGER NOT NULL CHECK(score >= 1 AND score <= 5),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(user_id, project_id)
    )
");
echo "  ✓ Table ratings\n";

$pgsql->exec("
    CREATE TABLE IF NOT EXISTS push_subscriptions (
        id SERIAL PRIMARY KEY,
        user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
        endpoint TEXT NOT NULL UNIQUE,
        p256dh VARCHAR(255),
        auth VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");
echo "  ✓ Table push_subscriptions\n\n";

// Migration des données
echo "Migration des données...\n";

// Fonction pour migrer une table
function migrateTable(PDO $sqlite, PDO $pgsql, string $table, array $columns): int {
    $rows = $sqlite->query("SELECT * FROM $table")->fetchAll(PDO::FETCH_ASSOC);
    $count = 0;
    
    if (empty($rows)) {
        return 0;
    }
    
    $cols = implode(', ', $columns);
    $placeholders = implode(', ', array_map(fn($c) => ":$c", $columns));
    
    $stmt = $pgsql->prepare("INSERT INTO $table ($cols) VALUES ($placeholders) ON CONFLICT DO NOTHING");
    
    foreach ($rows as $row) {
        $params = [];
        foreach ($columns as $col) {
            $params[":$col"] = $row[$col] ?? null;
        }
        try {
            $stmt->execute($params);
            $count++;
        } catch (PDOException $e) {
            echo "    Erreur ligne: " . $e->getMessage() . "\n";
        }
    }
    
    return $count;
}

// Migrer users
$count = migrateTable($sqlite, $pgsql, 'users', ['id', 'username', 'email', 'password', 'paroisse', 'role_choral']);
echo "  ✓ users: $count enregistrements\n";

// Réinitialiser la séquence
$maxId = $pgsql->query("SELECT COALESCE(MAX(id), 0) FROM users")->fetchColumn();
$pgsql->exec("SELECT setval('users_id_seq', $maxId)");

// Migrer projects
$count = migrateTable($sqlite, $pgsql, 'projects', [
    'id', 'user_id', 'title', 'description', 'thumbnail', 'media',
    'author', 'arranger', 'genre', 'tonality', 'moment_messe',
    'temps_liturgique', 'is_liturgical', 'voix', 'date_publication'
]);
echo "  ✓ projects: $count enregistrements\n";
$maxId = $pgsql->query("SELECT COALESCE(MAX(id), 0) FROM projects")->fetchColumn();
$pgsql->exec("SELECT setval('projects_id_seq', $maxId)");

// Migrer comments
$count = migrateTable($sqlite, $pgsql, 'comments', ['id', 'project_id', 'author', 'content', 'created_at']);
echo "  ✓ comments: $count enregistrements\n";
$maxId = $pgsql->query("SELECT COALESCE(MAX(id), 0) FROM comments")->fetchColumn();
$pgsql->exec("SELECT setval('comments_id_seq', $maxId)");

// Migrer subscribers
$count = migrateTable($sqlite, $pgsql, 'subscribers', ['id', 'email', 'created_at']);
echo "  ✓ subscribers: $count enregistrements\n";
$maxId = $pgsql->query("SELECT COALESCE(MAX(id), 0) FROM subscribers")->fetchColumn();
$pgsql->exec("SELECT setval('subscribers_id_seq', $maxId)");

// Migrer favorites
$count = migrateTable($sqlite, $pgsql, 'favorites', ['id', 'user_id', 'project_id', 'created_at']);
echo "  ✓ favorites: $count enregistrements\n";
$maxId = $pgsql->query("SELECT COALESCE(MAX(id), 0) FROM favorites")->fetchColumn();
$pgsql->exec("SELECT setval('favorites_id_seq', $maxId)");

// Migrer playlists
$count = migrateTable($sqlite, $pgsql, 'playlists', ['id', 'user_id', 'name', 'description', 'event_date', 'share_token', 'created_at']);
echo "  ✓ playlists: $count enregistrements\n";
$maxId = $pgsql->query("SELECT COALESCE(MAX(id), 0) FROM playlists")->fetchColumn();
$pgsql->exec("SELECT setval('playlists_id_seq', $maxId)");

// Migrer playlist_items
$count = migrateTable($sqlite, $pgsql, 'playlist_items', ['id', 'playlist_id', 'project_id', 'note', 'position']);
echo "  ✓ playlist_items: $count enregistrements\n";
$maxId = $pgsql->query("SELECT COALESCE(MAX(id), 0) FROM playlist_items")->fetchColumn();
$pgsql->exec("SELECT setval('playlist_items_id_seq', $maxId)");

// Migrer downloads (si existe)
try {
    $count = migrateTable($sqlite, $pgsql, 'downloads', ['id', 'project_id', 'user_id', 'ip_address', 'file_type', 'created_at']);
    echo "  ✓ downloads: $count enregistrements\n";
    $maxId = $pgsql->query("SELECT COALESCE(MAX(id), 0) FROM downloads")->fetchColumn();
    $pgsql->exec("SELECT setval('downloads_id_seq', $maxId)");
} catch (PDOException $e) {
    echo "  - downloads: table non existante dans SQLite\n";
}

// Migrer ratings (si existe)
try {
    $count = migrateTable($sqlite, $pgsql, 'ratings', ['id', 'user_id', 'project_id', 'score', 'created_at', 'updated_at']);
    echo "  ✓ ratings: $count enregistrements\n";
    $maxId = $pgsql->query("SELECT COALESCE(MAX(id), 0) FROM ratings")->fetchColumn();
    $pgsql->exec("SELECT setval('ratings_id_seq', $maxId)");
} catch (PDOException $e) {
    echo "  - ratings: table non existante dans SQLite\n";
}

echo "\n=== Migration terminée avec succès ! ===\n";
echo "\nPour utiliser PostgreSQL, assurez-vous que les variables suivantes sont définies dans .env:\n";
echo "  DB_HOST={$_ENV['DB_HOST']}\n";
echo "  DB_NAME={$_ENV['DB_NAME']}\n";
echo "  DB_USER={$_ENV['DB_USER']}\n";
echo "  DB_PASS=***\n";
