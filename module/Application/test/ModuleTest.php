<?php
declare(strict_types=1);

namespace ApplicationTest;

use Application\Domain\Environment;
use Application\Module;
use Application\RequestTimingListener;
use AppUnitBaseTests\AbstractAppBaseUnitTestCase;
use Laminas\EventManager\EventManager;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Application;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\RouteMatch;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use PHPUnit\Framework\MockObject\MockObject;


/**
 * @covers \Application\Module
 */
final class ModuleTest extends AbstractAppBaseUnitTestCase
{
    public function testLoadedConfigurationIsSerializable(): void
    {
        $config = (new Module())->getConfig();
        static::assertIsArray($config);
    }


    public function testOnBootstrap(): void
    {
        $event = new MvcEvent();
        $module = new Module();

        $eventManager = $this->createMock(EventManager::class);
        $environment = new Environment(\APPLICATION_ENV);
        $requestTimingListener = $this->createMock(RequestTimingListener::class);
        $serviceManager = $this->createMock(ServiceManager::class);
        /* @var $application Application|MockObject */
        $application = $this->createMock(Application::class);
        $event->setApplication($application);

        $application->expects(static::once())->method('getEventManager')->willReturn($eventManager);
        $application->expects(static::once())->method('getServiceManager')->willReturn($serviceManager);
        $eventManager->expects(static::exactly(2))->method('attach')
            ->withConsecutive(
                [
                    static::equalTo(MvcEvent::EVENT_DISPATCH_ERROR),
                    static::equalTo([$module, 'handleDispatchError']),
                    static::equalTo(-2)
                ],
                [
                    static::equalTo(MvcEvent::EVENT_RENDER_ERROR),
                    static::equalTo([$module, 'handleRenderError']),
                    static::equalTo(100)
                ]
            );
        $requestTimingListener->expects(static::once())->method('attach')->with($eventManager);

        $serviceManager->expects(static::exactly(2))
            ->method('get')
            ->with(
                static::logicalOr(
                    RequestTimingListener::class,
                    Environment::class
                )
            )
            ->willReturnMap(
                [
                    [RequestTimingListener::class, $requestTimingListener],
                    [Environment::class, $environment],
                ]
            );

        $module->onBootstrap($event);
    }


    public function testHandleDispatchErrorNonWithResponseWithoutRequestObject(): void
    {
        $event = new MvcEvent();
        $response = new Response();
        $event->setResponse($response)->setError('cannot-route');
        $module = new Module();
        $module->handleDispatchError($event);
        static::assertNull($event->getResult());
        $model = $event->getViewModel();
        static::assertInstanceOf(ViewModel::class, $model);
        static::assertEquals(
            [
            ],
            (array) $model->getVariables()
        );
        static::assertEquals(500, $response->getStatusCode());
    }


    /**
     * @dataProvider dataHandleDispatchError
     *
     * @param string $urlPath
     */
    public function testHandleDispatchErrorApiRoute(string $urlPath): void
    {
        $event = new MvcEvent();
        $request = new Request();
        $request->getUri()->setPath($urlPath);
        $event->setRequest($request)->setError('cannot-route');
        $module = new Module();
        $module->handleDispatchError($event);
        $actualResult = $event->getResult();
        static::assertInstanceOf(JsonModel::class, $actualResult);
        /* @var $actualResult JsonModel */
        static::assertEquals(
            [
                'message' => 'Unable to serve the request',
                'status' => 'error',
            ],
            (array) $actualResult->getVariables()
        );
        static::assertTrue($actualResult->terminate());
        static::assertSame($actualResult, $event->getViewModel());
    }


    /**
     * @dataProvider dataHandleDispatchError
     */
    public function testHandleDispatchErrorApiRouteWithReason(string $path): void
    {
        $reason = uniqid('reason', true);
        $event = new MvcEvent();
        $request = new Request();
        $request->getUri()->setPath($path);
        $event->setRequest($request)
            ->setError('cannot-route')
            ->setRouteMatch(new RouteMatch(['reason' => ['test' => $reason]]));
        $module = new Module();
        $module->handleDispatchError($event);
        $actualResult = $event->getResult();
        static::assertInstanceOf(JsonModel::class, $actualResult);
        /* @var $actualResult JsonModel */
        static::assertEquals(
            [
                'message' => 'Unable to serve the request',
                'status' => 'error',
            ],
            (array) $actualResult->getVariables()
        );
        static::assertTrue($actualResult->terminate());
        static::assertSame($actualResult, $event->getViewModel());
    }


    /**
     * @dataProvider dataHandleDispatchError
     */
    public function testHandleDispatchErrorApiRouteWithReasonFromSolvians(string $path): void
    {
        $reason = uniqid('reason', true);
        $event = new MvcEvent();
        $request = new Request();
        $request->getUri()->setPath($path);
        $event->setRequest($request)
            ->setError('cannot-route')
            ->setRouteMatch(new RouteMatch(['reason' => ['test' => $reason]]));
        $module = new Module();
        $this->setPropertyValue($module, 'isDebugging', true);
        $module->handleDispatchError($event);
        $this->setPropertyValue($module, 'isDebugging', false);
        $actualResult = $event->getResult();
        static::assertInstanceOf(JsonModel::class, $actualResult);
        /* @var $actualResult JsonModel */
        $variables = (array) $actualResult->getVariables();
        static::assertEquals('Unable to serve the request', $variables['message']);
        static::assertEquals('error', $variables['status']);
        static::assertEquals('cannot-route', $variables['Error from event (from Solvians)']);
        static::assertEquals(['reason' => ['test' => $reason]], $variables['RouteMatch (from Solvians)']);
        static::assertEquals(['test' => $reason], $variables['routerMatch Reason (from Solvians)']);
        static::assertTrue($actualResult->terminate());
        static::assertSame($actualResult, $event->getViewModel());
    }


    /**
     * @dataProvider dataHandleDispatchError
     */
    public function testHandleDispatchErrorApiRouteWithException(string $path): void
    {
        $message = uniqid('reason', true);
        $request = new Request();
        $request->getUri()->setPath($path);
        $event = new MvcEvent();
        $event->setRequest($request)->setError('cannot-route')->setParam('exception', new \Exception($message));
        $module = new Module();
        $module->handleDispatchError($event);
        $actualResult = $event->getResult();
        static::assertInstanceOf(JsonModel::class, $actualResult);
        /* @var $actualResult JsonModel */
        static::assertEquals(
            [
                'message' => 'Unable to serve the request',
                'status' => 'error',
            ],
            (array) $actualResult->getVariables()
        );
        static::assertTrue($actualResult->terminate());
        static::assertSame($actualResult, $event->getViewModel());
    }


    public function testCannotHandleDispatchError(): void
    {
        $event = new MvcEvent();
        $request = $this->createMock(Request::class);
        $request->method('getUri')->willReturn(null);
        $event->setRequest($request)
            ->setError('cannot-route');
        $module = new Module();
        $module->handleDispatchError($event);
        static::assertNull($event->getResult());
    }


    public function dataHandleDispatchError(): \Generator
    {
        yield '/page-api/test' => ['path' => '/page-api/test'];
        yield '/test' => ['path' => '/test'];
    }


    /**
     * @dataProvider dataHandleRenderError
     */
    public function testHandleRenderError(string $path): void
    {
        $event = new MvcEvent();
        $request = new Request();
        $request->getUri()->setPath($path);
        $event->setRequest($request)
            ->setError('cannot-render');
        $module = new Module();
        $module->handleRenderError($event);
        $actualResult = $event->getResult();
        static::assertInstanceOf(JsonModel::class, $actualResult);
        /* @var $actualResult JsonModel */
        static::assertEquals(
            [
                'message' => 'Unable to serve the request',
                'status' => 'error',
            ],
            (array) $actualResult->getVariables()
        );
        static::assertTrue($actualResult->terminate());
        static::assertSame($actualResult, $event->getViewModel());
    }


    public function dataHandleRenderError(): \Generator
    {
        yield '/page-api/test' => ['path' => '/page-api/test'];
        yield '/test' => ['path' => '/test'];
    }
}