<?php
// Error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Define root path
define('ROOT_PATH', dirname(__DIR__));

// Load environment variables
if (file_exists(ROOT_PATH . '/.env')) {
    $lines = file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        putenv($line);
    }
}

// Autoloader
require_once ROOT_PATH . '/vendor/autoload.php';

use App\Core\Router;
use App\Core\Request;

// Load configuration
require_once ROOT_PATH . '/config/app.php';

// Instantiate router
$router = new Router();

// Load routes
require_once ROOT_PATH . '/routes/web.php';

// Dispatch request
$request = new Request();
$router->dispatch($request);
