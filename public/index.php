<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

// Privremeni test
echo "<h1>Docker i PHP rade!</h1>";

// The rest of bootstrapping code...
// Router::dispatch($_SERVER['REQUEST_URI']);