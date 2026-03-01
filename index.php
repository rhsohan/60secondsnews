<?php
/**
 * Professional Bootstrap
 * This file serves as a fallback entry point if .htaccess routing is not enabled.
 * It transparently loads the main application from the public directory.
 */

// If we are at the root, load the public index
require_once __DIR__ . '/public/index.php';
