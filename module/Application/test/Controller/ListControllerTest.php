<?php
declare(strict_types=1);

namespace ApplicationTest\Controller;

use Application\Controller\ListController;
use Application\Domain\DomainExceptions\MongodbException;
use Application\Services\Instrument\InstrumentListService;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Application\Controller\ListController
 */
final class ListControllerTest extends \AppUnitBaseTests\AppControllerUnitTestCase
{
    /**
     * @var InstrumentListService|MockObject
     */
    private $instrumentListService;

    /**
     * @var ListController
     */
    private $listController;


    protected function setUp(): void
    {
        parent::setUp();
        $this->instrumentListService = $this->createMock(InstrumentListService::class);
        $this->listController = new ListController(
            $this->instrumentListService
        );
    }


    public function testListAction(): void
    {
        $this->mockParams($this->listController);
        $this->response = new \Laminas\Http\Response();
        $this->mockRequestResponse($this->listController);

        $bidString = '2.3';
        $this->params->expects(static::once())->method('fromRoute')
            ->with('bid')
            ->willReturn($bidString);
        $this->params->expects(static::once())->method('fromQuery')
            ->with('limit', 10)
            ->willReturn(20);

        $instruments = [
            ['instrument 1'],
            ['instrument 2'],
        ];
        $this->instrumentListService->expects(static::once())->method('expiredInstrumentsBefore')
            ->with(
                20,
                0,
                static::callback(function (\DateTime $input): bool {
                    $now = new \DateTime('now');
                    static::assertEqualsWithDelta($now->getTimestamp(), $input->getTimestamp(), 3.0);
                    return true;
                }),
                2.3
            )
            ->willReturn($instruments);

        $actual = $this->listController->listAction();
        static::assertEquals($instruments, $actual->getVariable('data'));
        static::assertEquals(200, $this->response->getStatusCode());
    }


    public function testListActionWithMongoException(): void
    {
        $this->mockParams($this->listController);
        $this->response = new \Laminas\Http\Response();
        $this->mockRequestResponse($this->listController);

        $bidString = '2.3';
        $this->params->expects(static::once())->method('fromRoute')
            ->with('bid')
            ->willReturn($bidString);
        $this->params->expects(static::once())->method('fromQuery')
            ->with('limit', 10)
            ->willReturn(20);

        $this->instrumentListService->expects(static::once())->method('expiredInstrumentsBefore')
            ->willThrowException(new MongodbException('my message'));

        $actual = $this->listController->listAction();
        static::assertEquals('error occurred', $actual->getVariable('message'));
        static::assertNull($actual->getVariable('data'));
        static::assertEquals(500, $this->response->getStatusCode());
    }


    public function testListActionWithThrowable(): void
    {
        $this->mockParams($this->listController);
        $this->response = new \Laminas\Http\Response();
        $this->mockRequestResponse($this->listController);

        $bidString = '2.3';
        $this->params->expects(static::once())->method('fromRoute')
            ->with('bid')
            ->willReturn($bidString);
        $this->params->expects(static::once())->method('fromQuery')
            ->with('limit', 10)
            ->willReturn(20);

        $this->instrumentListService->expects(static::once())->method('expiredInstrumentsBefore')
            ->willThrowException(new \TypeError('my message'));

        $actual = $this->listController->listAction();
        static::assertEquals('server error occurred', $actual->getVariable('message'));
        static::assertNull($actual->getVariable('data'));
        static::assertEquals(503, $this->response->getStatusCode());
    }


    public function testNullRatioAction(): void
    {
        $this->response = new \Laminas\Http\Response();
        $this->mockRequestResponse($this->listController);
        $actual = $this->listController->nullRatioAction();
        static::assertEquals(0, $actual->getVariable('data'));
        static::assertEquals(200, $this->response->getStatusCode());
    }
}
