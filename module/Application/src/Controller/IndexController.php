<?php
declare(strict_types=1);

namespace Application\Controller;

use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractRestfulController;
use Laminas\View\Model\JsonModel;
use Michelf\Markdown;
use Psr\Log\LoggerInterface;

/**
 * @method \Laminas\Http\PhpEnvironment\Response getResponse()
 * @method \Laminas\Http\PhpEnvironment\Request getRequest()
 */
class IndexController extends AbstractRestfulController
{
    /**
     * @var LoggerInterface
     */
    protected $logger;


    public function __construct(
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
    }


    public function get($id): JsonModel
    {
        $this->logger->info(
            sprintf(
                '%s: requested for ID:[%s]',
                __METHOD__,
                $id
            )
        );
        return new JsonModel([
            'data' => [
                'id' => $id
            ]
        ]);
    }


    /**
     * @return Response
     * @codeCoverageIgnore
     */
    public function getList(): Response
    {
        $this->layout()->setTerminal(true);
        $htmlContent = "<h1>Solvians Backend Challenge</h1>";
        $this->getResponse()->setContent($htmlContent);
        return $this->getResponse();
    }
}