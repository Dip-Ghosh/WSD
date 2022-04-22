<?php
declare(strict_types=1);

if (!defined('APPLICATION_ENV')) {
    define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ?: 'production'));
}

if (!defined('ENV_CONFIG_FILE')) {
    define('ENV_CONFIG_FILE', __DIR__ . '/autoload/' . \APPLICATION_ENV . '.php');
}

if (!defined('BACKEND_PATH')) {
    define('BACKEND_PATH', realpath(__DIR__ . '/..'));
}


