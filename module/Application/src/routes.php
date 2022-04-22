<?php
declare(strict_types=1);

use Application\Controller\IndexController;
use Application\Controller\ListController;
use Application\Module;
use Laminas\Router\Http\Literal;

return [
    'index-json' => [
        'type' => Literal::class,
        'priority' => Module::ROUTE_PRIORITY_MED,
        'options' => [
            'route' => '/',
            'defaults' => [
                /** @link \Application\Controller\IndexController::getList() */
                'controller' => IndexController::class,
            ],
        ],
    ],
    'index-json-id' => [
        'type' => \Laminas\Router\Http\Segment::class,
        'priority' => Module::ROUTE_PRIORITY_MED,
        'options' => [
            'route' => '/:id',
            'defaults' => [
                /** @link \Application\Controller\IndexController::get() */
                'controller' => IndexController::class,
            ],
        ],
    ],
    'instrument-list' => [
        'type' => \Laminas\Router\Http\Segment::class,
        'priority' => Module::ROUTE_PRIORITY_MED,
        'options' => [
            'route' => '/list[/bid/:bid]',
            'constraints' => [
                'bid' => '[0-9]\d*(\.\d+)?',
            ],
            'defaults' => [
                /** @link \Application\Controller\ListController::listAction() */
                'controller' => ListController::class,
                'action' => 'list',
            ],
        ],
    ],
    'instrument-null-bid-ask-ratio' => [
        'type' => \Laminas\Router\Http\Segment::class,
        'priority' => Module::ROUTE_PRIORITY_MED,
        'options' => [
            'route' => '/list/null-ratio',
            'defaults' => [
                /** @link \Application\Controller\ListController::nullRatioAction() */
                'controller' => ListController::class,
                'action' => 'null-ratio',
            ],
        ],
    ],
];
