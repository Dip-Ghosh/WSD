<?php
declare(strict_types=1);

namespace Application\Cli\Command;

use Application\Domain\DomainExceptions\MongodbException;
use Application\Model\InstrumentModel\InstrumentObject;
use Application\Services\Instrument\InstrumentsPersistence;
use Laminas\Cli\Command\AbstractParamAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @codeCoverageIgnore
 */
class FillInstrumentPersistenceCommand extends AbstractParamAwareCommand
{
    public const PARAM_COUNT = 'count';

    /**
     * @var InstrumentsPersistence
     */
    protected $persistence;


    public function __construct(
        string                 $name,
        InstrumentsPersistence $persistence
    )
    {
        parent::__construct($name);
        $this->persistence = $persistence;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $instrumentDocuments = [];
        $count = (int) $input->getOption(self::PARAM_COUNT);
        if ($count <= 0) {
            $output->write('Require count param > 0' . PHP_EOL);
            return 1;
        }
        $thisYear = (int) (new \DateTime())->format('Y');
        while ($count--) {
            $bid = round(9 * lcg_value(), 2);
            $ask = round(9 * lcg_value(), 2);
            $expiry = (new \DateTime('now', new \DateTimeZone('UTC')))->setDate(
                [$thisYear - 1, $thisYear, $thisYear + 1][random_int(0, 2)],
                range(1, 12)[random_int(0, 11)],
                range(1, 28)[random_int(0, 27)]
            );
            $instrumentDocuments[] = (new InstrumentObject('isin' . random_int(100000, 200000)))
                ->setAsk(([null] + array_fill(0, 5, $bid))[random_int(0, 4)])
                ->setBid(([null] + array_fill(0, 5, $ask))[random_int(0, 4)])
                ->setExpiry(([null] + array_fill(0, 5, $expiry))[random_int(0, 4)])
                ->toMongoDocument();
        }
        try {
            $inserted = $this->persistence->insertMany($instrumentDocuments);
        }
        catch (MongodbException $exception) {
            $output->write(
                sprintf(
                    'Got exception of type: [%s] and message: [%s]',
                    get_class($exception),
                    $exception->getMessage()
                ) . PHP_EOL
            );
            return 1;
        }
        $output->write("inserted {$inserted} documents" . PHP_EOL);
        return 0;
    }
}