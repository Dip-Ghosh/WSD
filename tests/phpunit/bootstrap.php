<?php
declare(strict_types=1);

// Everything is relative to the application root (backend-relaunch/)
chdir(dirname(dirname(__DIR__)));

define('APPLICATION_ENV', \Application\Domain\Environment::TEST_ENV);
require_once __DIR__ . '/../../config/define-constants.php';
require_once __DIR__ . '/../../config/get-autoloader.php';
require_once __DIR__ . '/../../vendor/autoload.php';
