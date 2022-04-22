<?php
declare(strict_types=1);

namespace Application;

use Application\Controller\IndexController;
use Application\Controller\ListController;
use Application\Domain\Environment;
use Application\Services\Instrument\InstrumentListService;
use Application\View\Model\AppJsonModel;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\ResponseInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Module
 * The routing for application is defined here
 *
 * @package Application
 */
final class Module implements \Laminas\ModuleManager\Feature\ConfigProviderInterface
{
    public const ROUTE_PRIORITY_HIGH = 200;
    public const ROUTE_PRIORITY_MED = 100;
    public const ROUTE_PRIORITY_LOW = 50;
    public const ROUTE_PRIORITY_DEFAULT = 0;
    public const ROUTE_PRIORITY_NEGATIVE_LOW = -50;

    /**
     * @var bool
     */
    private static $isDebugging = false;

    /**
     * @var Environment
     */
    private $environment;


    public function getConfig(): array
    {
        return [
            'laminas-cli' => [
                'commands' => [
                    'app:fill-sins' => \Application\Cli\Command\FillInstrumentPersistenceCommand::class,
                ],
            ],

            'router' => [
                'routes' => require __DIR__ . '/routes.php', // 'routes'
            ], // 'router'

            'controllers' => [
                'factories' => [
                    IndexController::class => static function (ContainerInterface $container) {
                        return new IndexController($container->get(LoggerInterface::class));
                    },
                    ListController::class => static function (ContainerInterface $container) {
                        return new ListController($container->get(InstrumentListService::class));
                    },
                ],
            ],
            'service_manager' => [
                'factories' => require __DIR__ . '/serviceManagerFactories.php',
                'aliases' => [],
            ],
            'view_manager' => [
                'display_not_found_reason' => true,
                'display_exceptions' => true,
                'not_found_template' => 'app-errors/apperror-apphtml',
                'exception_template' => 'app-errors/apperror-apphtml',
                'template_path_stack' => [
                    __DIR__ . '/../view',
                    __DIR__ . '/../templates',
                ],
                'template_map' => [
                    'solvians/empty-layout' => __DIR__ . '/../templates/empty-layout.phtml',
                ],
                'strategies' => [
                    'ViewJsonStrategy',
                ],
            ],
        ];
    }


    public function onBootstrap(MvcEvent $e): void
    {
        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach(
            MvcEvent::EVENT_DISPATCH_ERROR,
            [$this, 'handleDispatchError'],
            -2
        );
        $eventManager->attach(
            MvcEvent::EVENT_RENDER_ERROR,
            [$this, 'handleRenderError'],
            100
        );

        /**
         * @var ContainerInterface $container
         */
        $container = $e->getApplication()->getServiceManager();

        /** @var RequestTimingListener $requestTimingListener */
        $requestTimingListener = $container->get(RequestTimingListener::class);
        $requestTimingListener->attach($eventManager);

        $this->environment = $container->get(Environment::class);
        self::$isDebugging = $this->environment->isDev();
    }


    public static function newAppJsonModel($variables = null, $options = null): AppJsonModel
    {
        return new AppJsonModel($variables, $options, (bool) self::$isDebugging);
    }


    /**
     * @param MvcEvent $e
     *
     * @internal this method needs to be public since we use it with $eventManager->attach()
     */
    public function handleDispatchError(MvcEvent $e): ?ResponseInterface
    {
        $response = $e->getResponse();
        if ($response instanceof \Laminas\Http\Response) {
            $response->setStatusCode(\Laminas\Http\Response::STATUS_CODE_500);
        }

        $request = $e->getRequest();
        if (
            !$request instanceof Request
            || !$request->getUri()
        ) {
            return null;
        }
        $error = $e->getError();
        if (!empty($error)) {
            $model = self::newAppJsonModel(
                [
                    'message' => 'Unable to serve the request',
                ]
            )->setStatusError();
            $model->setVariableFromSolvians('generated in', __FILE__ . ':' . __LINE__);
            $model->setVariableFromSolvians('Error from event', $error);
            $model->setVariableFromSolvians('RouteMatch', $e->getRouteMatch() ? $e->getRouteMatch()->getParams() : null);

            if ($e->getRouteMatch() !== null && isset($e->getRouteMatch()->getParams()['reason'])) {
                $model->setVariableFromSolvians('routerMatch Reason', $e->getRouteMatch()->getParams()['reason']);
            }
            if (isset($e->getParams()['exception']) && $e->getParams()['exception'] instanceof \Throwable) {
                $model->setThrowableFromSolvians($e->getParams()['exception']);
            }
            $model->setTerminal(true);
            $e->setResult($model);
            $e->setViewModel($model);
        }
        return null;
    }


    /**
     * @param MvcEvent $e
     *
     * @internal this method needs to be public since we use it with $eventManager->attach()
     */
    public function handleRenderError(MvcEvent $e): void
    {
        $request = $e->getRequest();
        if (
            !$request instanceof Request
            || !$request->getUri()
        ) {
            return;
        }
        $error = $e->getError();
        if (!empty($error)) {
            $model = self::newAppJsonModel(
                [
                    'message' => 'Unable to serve the request',
                ]
            )->setStatusError();
            $model->setVariableFromSolvians('model created in', __METHOD__);
            $model->setVariableFromSolvians('error from event', $error);
            $model->setTerminal(true);
            $e->setResult($model);
            $e->setViewModel($model);
        }
        $response = $e->getResponse();
        if ($response instanceof Response) {
            $response->setStatusCode(Response::STATUS_CODE_500);
        }
    }
}

