<?php

declare(strict_types = 1);

namespace Application\Controller;

use Application\Services\Portfolio\PortfolioService;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractRestfulController;
use Laminas\View\Model\JsonModel;
use Michelf\Markdown;
use Psr\Log\LoggerInterface;

/**
 * @method \Laminas\Http\PhpEnvironment\Response getResponse()
 * @method \Laminas\Http\PhpEnvironment\Request getRequest()
 */
class PortfolioController extends AbstractRestfulController
{
    protected $portfolioService;

    public function __construct(PortfolioService $portfolioService)
    {
        $this->portfolioService = $portfolioService;
    }

    public function getPortfolios()
    {
        $showProfits          = $this->params()->fromQuery('showProfits', 0);
        $structureId          = $this->params()->fromQuery('structureId', null);
        $instruments          = $this->portfolioService->getInstruments();
        $instrumentProperties = $this->portfolioService->getInstrumentProperties();
        $portfolios           = $this->portfolioService->mergeData($instruments, $instrumentProperties);
        $filteredPortfolios   = $this->portfolioService->filterPortfolios($portfolios, $showProfits, $structureId);

        return new JsonModel($filteredPortfolios);
    }
}