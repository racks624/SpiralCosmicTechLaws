<?php
/**
 * SpiralCosmicTechLaws - Divine Cosmic Enterprise Framework
 * Industrial-Grade RedTeam & C2 Platform
 * 
 * @package SpiralCosmicTechLaws
 * @version 3.0.0 - Cosmic UI
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define application root
define('ROOT_PATH', realpath(__DIR__ . '/..'));
define('PUBLIC_PATH', __DIR__);

// Load environment variables
if (file_exists(ROOT_PATH . '/.env')) {
    $lines = file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        putenv($line);
    }
}

// Register autoloader
require_once ROOT_PATH . '/vendor/autoload.php';

use App\Core\Router;
use App\Core\Request;

// Load configuration
require_once ROOT_PATH . '/config/app.php';

// Set timezone
date_default_timezone_set(getenv('TIMEZONE') ?: 'UTC');

// Global exception handler with cosmic‑friendly output
set_exception_handler(function ($e) {
    $debug = filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN);
    if ($debug) {
        http_response_code(500);
        echo '<!DOCTYPE html><html><head><title>Cosmic Error</title>';
        echo '<style>body{background:#1a0b3a;color:#a7f3d0;font-family:"Exo 2",sans-serif;padding:2rem;}h1{color:#fbbf24;}pre{background:rgba(0,0,0,0.3);padding:1rem;border-radius:0.5rem;border:1px solid #00ff88;}</style>';
        echo '</head><body>';
        echo '<h1>🌌 Cosmic Exception</h1>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>File:</strong> ' . $e->getFile() . ':' . $e->getLine() . '</p>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
        echo '</body></html>';
    } else {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Internal Server Error',
            'message' => $e->getMessage()
        ]);
    }
});

// Instantiate router
$router = new Router();

// Load routes
require_once ROOT_PATH . '/routes/web.php';

// Create request and dispatch
$request = new Request();
$router->dispatch($request);
