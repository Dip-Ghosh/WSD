<?php
declare(strict_types=1);

namespace ApplicationTest;

use Application\RequestTimingListener;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\JsonModel;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Application\RequestTimingListener
 */
final class RequestTimingListenerTest extends TestCase
{
    public function testAttach(): void
    {
        $listener = new RequestTimingListener(false);
        $events = $this->createMock(\Laminas\EventManager\EventManager::class);
        $events->expects(static::once())->method('attach')
            ->with(MvcEvent::EVENT_RENDER, [$listener, 'requestEnding'], -1000);
        $listener->attach($events, -9);
    }


    public function testRequestEndingNotFromSolviansAndNoStartTime(): void
    {
        $result = new JsonModel();
        $mvcEvent = new MvcEvent();
        $mvcEvent->setResult($result);

        $listener = new RequestTimingListener(false);
        $listener->requestEnding($mvcEvent);
        static::assertNull($result->getVariable(RequestTimingListener::RESPONSE_KEY));
    }


    public function testRequestEndingFromSolviansAndNoStartTime(): void
    {
        $result = new JsonModel();
        $mvcEvent = new MvcEvent;
        $mvcEvent->setResult($result);

        $listener = new RequestTimingListener(true);
        $listener->requestEnding($mvcEvent);
        static::assertEquals('no start time', $result->getVariable(RequestTimingListener::RESPONSE_KEY));
    }


    public function testRequestEndingFromSolviansAndNoStartTimeWithInitialValueExists(): void
    {
        $result = new JsonModel(
            [RequestTimingListener::RESPONSE_KEY => 'initial value']
        );
        $mvcEvent = new MvcEvent;
        $mvcEvent->setResult($result);

        $listener = new RequestTimingListener(true);
        $listener->requestEnding($mvcEvent);
        static::assertEquals('initial value', $result->getVariable(RequestTimingListener::RESPONSE_KEY));
    }


    public function testRequestEndingFromSolviansWithStartTime(): void
    {
        $result = new JsonModel();
        $mvcEvent = new MvcEvent;
        $mvcEvent->setResult($result);

        $listener = new RequestTimingListener(true);
        $listener->requestStarting($mvcEvent);
        $listener->requestEnding($mvcEvent);
        $actual = $result->getVariable(RequestTimingListener::RESPONSE_KEY);

        static::assertThat(
            $actual,
            static::matchesRegularExpression(
                '@^[0-9].[0-9]{4} ms$@'
            )
        );
    }
}