<?php
declare(strict_types=1);

namespace AppUnitBaseTests;

use Laminas\Mvc\Controller\AbstractController;
use Laminas\Mvc\Controller\Plugin\Forward;
use Laminas\Mvc\Controller\Plugin\Params;
use Laminas\Mvc\Controller\Plugin\Redirect;
use PHPUnit\Framework\MockObject\MockObject;

abstract class AppControllerUnitTestCase extends AbstractAppBaseUnitTestCase
{
    /**
     * @var MockObject|\Laminas\Http\Request|\Laminas\Stdlib\RequestInterface|null
     */
    protected $request;

    /**
     * @var MockObject|\Laminas\Http\Response|\Laminas\Stdlib\ResponseInterface|null
     */
    protected $response;

    /**
     * @var MockObject|Params|null
     */
    protected $params;

    /**
     * @var Redirect
     */
    protected $redirect;

    /**
     * @var Forward
     */
    protected $forward;


    protected function mockParams(AbstractController $controller): void
    {
        $this->params = $this->createMock(Params::class);
        $this->params->method('__invoke')->willReturnSelf();
        $controller->getPluginManager()->setService('params', $this->params);
    }


    protected function mockRedirect(AbstractController $controller): void
    {
        $this->redirect = $this->createMock(Redirect::class);
        $controller->getPluginManager()->setService('redirect', $this->redirect);
    }


    protected function mockForward(AbstractController $controller): void
    {
        $this->forward = $this->createMock(Forward::class);
        $controller->getPluginManager()->setService('forward', $this->forward);
    }


    /**
     * @param AbstractController $controller
     * @param string             $requestClass  Enum:{'Laminas\Http\Request', 'Laminas\Stdlib\RequestInterface', 'Laminas\Http\PhpEnvironment\Request'}
     * @param string             $responseClass Enum:{'Laminas\Http\Response', 'Laminas\Stdlib\ResponseInterface', 'Laminas\Http\PhpEnvironment\Response'}
     *
     * @throws \ReflectionException
     */
    protected function mockRequestResponse(
        AbstractController $controller,
        string             $requestClass = \Laminas\Http\Request::class,
        string             $responseClass = \Laminas\Http\Response::class
    ): void
    {
        $this->request = $this->request ?: $this->createMock($requestClass);
        $this->response = $this->response ?: $this->createMock($responseClass);

        $reflectionReq = new \ReflectionProperty($controller, 'request');
        $reflectionReq->setAccessible(true);
        $reflectionReq->setValue($controller, $this->request);

        $reflectionRes = new \ReflectionProperty($controller, 'response');
        $reflectionRes->setAccessible(true);
        $reflectionRes->setValue($controller, $this->response);
    }


    /**
     * @param AbstractController $controller
     *
     * @throws \ReflectionException
     */
    protected function setRequestResponse(
        AbstractController $controller
    ): void
    {
        $reflectionReq = new \ReflectionProperty($controller, 'request');
        $reflectionReq->setAccessible(true);
        $reflectionReq->setValue($controller, $this->request);

        $reflectionRes = new \ReflectionProperty($controller, 'response');
        $reflectionRes->setAccessible(true);
        $reflectionRes->setValue($controller, $this->response);
    }
}