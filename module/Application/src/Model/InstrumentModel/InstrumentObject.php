<?php
declare(strict_types=1);

namespace Application\Model\InstrumentModel;

use Application\Model\InstrumentModel\Exceptions\InvalidInstrumentValueException;
use Application\Util\DatetimeUtil;

class InstrumentObject implements \JsonSerializable
{
    public const isin = 'isin';
    public const bid = 'bid';
    public const ask = 'ask';
    public const expiry = 'expiry';
    private const DATE_PROPERTIES = [self::expiry];

    protected $properties = [];


    public function __construct(
        string $isin
    )
    {
        $this->setIsin($isin);
    }


    /**
     * @param array $mongoDocument
     *
     * @return static
     * @throws InvalidInstrumentValueException
     */
    public static function fromMongoDocument(array $mongoDocument): self
    {
        if (!is_string($mongoDocument['isin'] ?? null)) {
            throw new InvalidInstrumentValueException('key "isin" is not part of the document');
        }
        try {
            $return = new self($mongoDocument['isin']);
            $return->setBid($mongoDocument['bid'] ?? null);
            $return->setAsk($mongoDocument['ask'] ?? null);
            $return->setExpiry(DatetimeUtil::toDateTime($mongoDocument['expiry'] ?? null));
        }
        catch (\TypeError $exception) {
            throw new InvalidInstrumentValueException(
                sprintf('invalid value type received in method: [%s]', __METHOD__),
                $exception->getCode(),
                $exception
            );
        }
        return $return;
    }


    public function toMongoDocument(): array
    {
        return array_map(
                [DatetimeUtil::class, 'toMongodbUtcDateTime'],
                array_intersect_key($this->properties, array_flip(self::DATE_PROPERTIES))
            ) + $this->jsonSerialize();
    }


    public function jsonSerialize()
    {
        return
            array_map(
                static function (?\DateTime $datetime): ?string {
                    return $datetime ? $datetime->format('c') : null;
                },
                array_intersect_key($this->properties, array_flip(self::DATE_PROPERTIES))
            )
            +
            $this->properties;
    }


    /**
     * @param string $property
     * @param        $value
     *
     * @return $this
     * @codeCoverageIgnore
     */
    protected function setProp(string $property, $value): self
    {
        $this->properties[$property] = $value;
        return $this;
    }


    /**
     * @codeCoverageIgnore
     */
    protected function getProp(string $property)
    {
        return $this->properties[$property] ?? null;
    }


    /**
     * @codeCoverageIgnore
     */
    public function setIsin(string $isin): self
    {
        return $this->setProp(self::isin, $isin);
    }


    /**
     * @codeCoverageIgnore
     */
    public function getIsin(): string
    {
        return $this->getProp(self::isin);
    }


    /**
     * @codeCoverageIgnore
     */
    public function setBid(?float $bid): self
    {
        return $this->setProp(self::bid, $bid);
    }


    /**
     * @codeCoverageIgnore
     */

    public function getBid(): ?float
    {
        return $this->getProp(self::bid);
    }


    /**
     * @codeCoverageIgnore
     */
    public function setAsk(?float $ask): self
    {
        return $this->setProp(self::ask, $ask);

    }


    /**
     * @codeCoverageIgnore
     */
    public function getAsk(): ?float
    {
        return $this->getProp(self::ask);
    }


    /**
     * @codeCoverageIgnore
     */
    public function setExpiry(?\DateTime $expiry): self
    {
        return $this->setProp(self::expiry, $expiry);
    }


    /**
     * @codeCoverageIgnore
     */
    public function getExpiry(): ?\DateTime
    {
        return $this->getProp(self::expiry);
    }
}