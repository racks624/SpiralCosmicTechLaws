#!/usr/bin/env php
<?php
/**
 * Database Migration Runner
 */

// Define root path BEFORE including anything
define('ROOT_PATH', realpath(__DIR__ . '/..'));

require_once ROOT_PATH . '/vendor/autoload.php';
require_once ROOT_PATH . '/config/database.php';

use App\Core\Model;

// Ensure the migrations table exists
$createMigrationsTable = "
CREATE TABLE IF NOT EXISTS migrations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    migration VARCHAR(255) NOT NULL UNIQUE,
    applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
";
try {
    Model::query($createMigrationsTable);
} catch (PDOException $e) {
    die("Failed to create migrations table: " . $e->getMessage());
}

// Get list of already applied migrations
$stmt = Model::query("SELECT migration FROM migrations");
$applied = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get all migration files
$migrationFiles = glob(ROOT_PATH . '/storage/migrations/*.sql');
sort($migrationFiles);

$newMigrations = array_filter($migrationFiles, function($file) use ($applied) {
    return !in_array(basename($file), $applied);
});

if (empty($newMigrations)) {
    echo "No pending migrations.\n";
    exit(0);
}

echo "Applying " . count($newMigrations) . " migrations:\n";
foreach ($newMigrations as $file) {
    $migrationName = basename($file);
    echo "  - $migrationName ... ";
    try {
        $sql = file_get_contents($file);
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($statements as $stmt) {
            if (!empty($stmt)) {
                Model::query($stmt);
            }
        }
        Model::query("INSERT INTO migrations (migration) VALUES (?)", [$migrationName]);
        echo "OK\n";
    } catch (Exception $e) {
        echo "FAILED: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "All migrations applied successfully.\n";
