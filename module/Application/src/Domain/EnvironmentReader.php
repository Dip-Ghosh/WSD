<?php
declare(strict_types=1);

namespace Application\Domain;

use Application\Domain\DomainExceptions\EnvironmentVariableNotFoundException;

/**
 * @codeCoverageIgnore
 */
final class EnvironmentReader
{
    /**
     * @return Environment
     * @throws DomainExceptions\InvalidEnvironmentException
     * @throws EnvironmentVariableNotFoundException
     */
    public function getEnvironment(): Environment
    {
        return new Environment($this->readFromEnvironment('APPLICATION_ENV'));
    }


    /**
     * @param string $key
     *
     * @return string
     * @throws EnvironmentVariableNotFoundException
     */
    private function readFromEnvironment(string $key): string
    {
        if (defined($key)) {
            return get_defined_constants(true)['user'][$key];
        }
        $envVariable = getenv($key);
        if ($envVariable === false) {
            throw new EnvironmentVariableNotFoundException(
                sprintf('The requested environment variable "%s" was not found', $key)
            );
        }

        return $envVariable;
    }
}
