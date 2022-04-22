<?php
declare(strict_types=1);

namespace Application\Domain\DomainExceptions;

/**
 * @codeCoverageIgnore
 */
class MongodbException extends DomainException
{
    public static function fromException(
        \Throwable $original
    ): self
    {
        return new self($original->getMessage(), $original->getCode(), $original);
    }
}