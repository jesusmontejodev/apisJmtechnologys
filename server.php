<?php
/**
 * Laravel Development Server Router
 * This file allows the built-in PHP server to handle Laravel routing
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Serve static files directly
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

// Require the Laravel app
require_once __DIR__ . '/public/index.php';
