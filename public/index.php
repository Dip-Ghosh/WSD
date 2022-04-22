<?php
declare(strict_types=1);

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(__DIR__);

$application = require __DIR__ . '/../config/initApplication.php';
// Run the application!
$application->run();
