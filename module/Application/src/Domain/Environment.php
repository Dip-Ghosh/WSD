<?php
declare(strict_types=1);

namespace Application\Domain;

use Application\Domain\DomainExceptions\InvalidEnvironmentException;

/**
 * @codeCoverageIgnore
 */
final class Environment
{
    public const DEV_ENV = 'development';
    public const TEST_ENV = 'testing';
    public const PROD_ENV = 'production';
    public const STAGE_ENV = 'staging';
    public const VALID_ENVIRONMENTS = [self::DEV_ENV, self::TEST_ENV, self::STAGE_ENV, self::PROD_ENV];

    private $value;


    /**
     * @param string $environment
     *
     * @throws InvalidEnvironmentException
     */
    public function __construct(string $environment)
    {
        $this->ensure($environment);
        $this->value = $environment;
    }


    public function asString(): string
    {
        return $this->value;
    }


    public function isDev(): bool
    {
        return $this->value === self::DEV_ENV;
    }


    public function isTest(): bool
    {
        return $this->value === self::TEST_ENV;
    }


    public function isProd(): bool
    {
        return $this->value === self::PROD_ENV;
    }


    public function isStage(): bool
    {
        return $this->value === self::STAGE_ENV;
    }


    private function ensure(string $environment): void
    {
        if (!in_array($environment, self::VALID_ENVIRONMENTS, true)) {
            throw new InvalidEnvironmentException(
                sprintf('Invalid environment given: [%s]', $environment)
            );
        }
    }
}
