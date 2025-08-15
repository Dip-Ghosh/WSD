<?php
declare(strict_types=1);

namespace Application;

use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\JsonModel;

class RequestTimingListener extends AbstractListenerAggregate
{
    public const RESPONSE_KEY = 'requestTime';

    /**
     * @var bool
     */
    protected $isRequestFromSolvians;

    /**
     * @var float|null
     */
    protected $requestStartMicroTime = null;


    public function __construct(
        bool $isRequestFromSolvians
    )
    {
        $this->isRequestFromSolvians = $isRequestFromSolvians;
    }


    /**
     * {@inheritdoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1): void
    {
        $this->requestStartMicroTime = null;

        $events->attach(MvcEvent::EVENT_ROUTE, [$this, 'requestStarting'], -1000);
        $events->attach(MvcEvent::EVENT_RENDER, [$this, 'requestEnding'], -1000);
    }


    /**
     * @param MvcEvent $mvcEvent
     */
    public function requestStarting(MvcEvent $mvcEvent): void
    {
        $this->requestStartMicroTime = microtime(true);
    }


    /**
     * @param MvcEvent $mvcEvent
     */
    public function requestEnding(MvcEvent $mvcEvent): void
    {
        $model = $mvcEvent->getResult();
        if ($model instanceof JsonModel) {
            if ($this->isRequestFromSolvians && !$model->getVariable(self::RESPONSE_KEY)) {
                $model->setVariable(self::RESPONSE_KEY, $this->getResponseTimeInMSeconds());
            }
        }
    }


    private function getResponseTimeInMSeconds(): string
    {
        return $this->requestStartMicroTime !== null
            ? number_format(
                1000 * (microtime(true) - $this->requestStartMicroTime),
                4,
                '.',
                ''
            ) . ' ms'
            : 'no start time';
    }
}
