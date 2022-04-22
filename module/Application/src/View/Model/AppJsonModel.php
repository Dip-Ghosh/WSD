<?php
declare(strict_types=1);

namespace Application\View\Model;

use Laminas\Stdlib\ArrayUtils;

/**
 * @codeCoverageIgnore
 */
final class AppJsonModel extends \Laminas\View\Model\JsonModel
{
    /**
     * Response key - user friendly message: error, status update, confirmation etc.
     */
    public const VAR_KEY_MESSAGE = 'message';

    /**
     * Response key - status code (needed for frontend)
     */
    public const VAR_KEY_STATUS = 'status';

    /**
     * Response key - state string (needed for frontend and is used in UBS-Solo)
     */
    public const VAR_KEY_STATE = 'state';

    /**
     * Response key - meta data
     */
    public const VAR_KEY_META = 'meta';

    public const STATUS_OK = 'OK';
    public const STATUS_ERROR = 'error';

    /**
     * @var bool
     */
    protected $isRequestFromSolvians;


    public function __construct(
        $variables = null,
        $options = null,
        bool $isRequestFromSolvians = false
    )
    {
        parent::__construct($variables, $options);
        $this->isRequestFromSolvians = $isRequestFromSolvians;
    }


    public function setStatusOk()
    {
        $this->setMessage(self::STATUS_OK);
        return $this->setStatus(self::STATUS_OK);
    }


    public function isStatusOk(): bool
    {
        return $this->getVariable(self::VAR_KEY_STATUS) === self::STATUS_OK;
    }


    /**
     * Set response message
     *
     * @param string $message response message
     *
     * @return self
     */
    public function setMessage(string $message)
    {
        return $this->setVariable(self::VAR_KEY_MESSAGE, $message);
    }


    /**
     * Set response status
     *
     * @param string $status response status
     *
     * @return self
     */
    public function setStatus(string $status)
    {
        return $this->setVariable(self::VAR_KEY_STATUS, $status);
    }


    /**
     * Set response meta data
     *
     * @param mixed $meta meta data
     *
     * @return self
     */
    public function setMeta($meta)
    {
        return $this->setVariable(self::VAR_KEY_META, $meta);
    }


    /**
     * @return static
     */
    public function setStateStatusOk(): self
    {
        return $this
            ->setVariable(self::VAR_KEY_STATUS, self::STATUS_OK)
            ->setVariable(self::VAR_KEY_STATE, self::STATUS_OK);
    }


    /**
     * @return static
     */
    public function setStatusError(): self
    {
        $this->setStatus(self::STATUS_ERROR);
        return $this;
    }


    /**
     * @return static
     */
    public function setStateStatusError(): self
    {
        return $this
            ->setVariable(self::VAR_KEY_STATUS, self::STATUS_ERROR)
            ->setVariable(self::VAR_KEY_STATE, self::STATUS_ERROR);
    }


    /**
     * @codeCoverageIgnore
     *
     * @param string|null $default
     *
     * @return string|null
     */
    public function getMessage(?string $default = null): ?string
    {
        return $this->getVariable(self::VAR_KEY_MESSAGE, $default);
    }


    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return static
     */
    public function setVariable($name, $value): self
    {
        parent::setVariable($name, $value);
        return $this;
    }


    /**
     * @param string $name
     * @param mixed  $value
     * @param bool   $addFromSolviansString
     *
     * @return static
     */
    public function setVariableFromSolvians(string $name, $value, bool $addFromSolviansString = true): self
    {
        if ($this->isRequestFromSolvians) {
            parent::setVariable(
                $name . ($addFromSolviansString ? ' (from Solvians)' : ''),
                $value
            );
        }
        return $this;
    }


    public function setThrowableFromSolvians(\Throwable $throwable): self
    {
        if (!$this->isRequestFromSolvians) {
            return $this;
        }
        $previous = $throwable;
        $exceptions = [];
        $processed = [];
        do {
            if (!in_array(spl_object_hash($previous), $processed)) {
                $exceptions[] = [
                    'type' => get_class($previous),
                    'message' => $previous->getMessage(),
                    'file' => $previous->getFile(),
                    'line' => $previous->getLine(),
                    'trace' => explode("\n", $previous->getTraceAsString()),
                ];
                $processed[] = spl_object_hash($previous);
                $previous = $throwable->getPrevious();
            }
            else {
                // we have a loop, this is encountered and then coded like this.
                break;
            }
        } while ($previous);
        return $this->setVariableFromSolvians('exceptions', $exceptions);
    }


    /**
     * @param mixed $value
     *
     * @return self
     */
    public function setData($value): self
    {
        return $this->setVariable('data', $value);
    }


    /**
     * @param string $name
     * @param mixed  $value
     * @param bool   $overwrite
     *
     * @return static
     */
    public function setDataVariable(string $name, $value, bool $overwrite = false): self
    {
        $arrayToBeSet = [
            $name => $value,
        ];

        if (!$overwrite) {
            $arrayToBeSet = ArrayUtils::merge(
                (array) $this->getVariable('data', []),
                $arrayToBeSet
            );
        }

        return $this->setVariable('data', $arrayToBeSet);
    }


    /**
     * @param string $name
     * @param mixed  $value
     * @param bool   $addFromSolviansString
     * @param bool   $overwrite
     *
     * @return static
     */
    public function setDataVariableFromSolvians(
        string $name,
               $value,
        bool   $addFromSolviansString = true,
        bool   $overwrite = false
    ): self
    {
        $arrayToBeSet = [
            $name . ($addFromSolviansString ? ' (from Solvians)' : '') => $value,
        ];

        if (!$overwrite) {
            $arrayToBeSet = ArrayUtils::merge(
                (array) $this->getVariable('data', []),
                $arrayToBeSet
            );
        }

        return $this->setVariableFromSolvians('data', $arrayToBeSet, false);
    }
}