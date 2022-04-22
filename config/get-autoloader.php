<?php
declare(strict_types=1);

require_once __DIR__ . '/define-constants.php';

/**
 * Run in lambda to prevent global namespace alteration
 */
return (function (): \Composer\Autoload\ClassLoader {
    /**
     * @var \Composer\Autoload\ClassLoader $autoloader
     */
    return require \BACKEND_PATH . '/vendor/autoload.php';
})();
