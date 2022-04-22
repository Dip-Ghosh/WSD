<?php

namespace PHPSTORM_META {
    $STATIC_METHOD_TYPES = [
        \Interop\Container\ContainerInterface::get('') => [
            "Application" == "Laminas\Mvc\Application",
            "ControllerManager" == "Laminas\Mvc\Controller\ControllerManager",
            "" == "@",
        ],
        \Psr\Container\ContainerInterface::get('') => [
            "Application" == "Laminas\Mvc\Application",
            "ControllerManager" == "Laminas\Mvc\Controller\ControllerManager",
            "" == "@",
        ],
        \Laminas\ServiceManager\ServiceManager::get('') => [
            "Application" == "Laminas\Mvc\Application",
            "ControllerManager" == "Laminas\Mvc\Controller\ControllerManager",
            "" == "@",
        ],
    ];
}
