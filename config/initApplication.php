<?php
declare(strict_types=1);

return (function (): \Laminas\Mvc\Application {
    require_once __DIR__ . '/get-autoloader.php';

    $appConfig = require __DIR__ . '/application.config.php';

    if (file_exists(\ENV_CONFIG_FILE)) {
        // File only exists in dev-end, it is excluded by .gitignore
        $appConfig = \Laminas\Stdlib\ArrayUtils::merge($appConfig, require \ENV_CONFIG_FILE);
    }

    $application = \Laminas\Mvc\Application::init($appConfig);
    return $application;
})();
