<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Debug\ExceptionHandler;
use CodeIgniter\Debug\ExceptionHandlerInterface;
use Psr\Log\LogLevel;
use Throwable;

/**
 * Setup how the exception handler works.
 */
class Exceptions extends BaseConfig
{
    /**
     * LOG EXCEPTIONS?
     */
    public bool $log = true;

    /**
     * DO NOT LOG STATUS CODES
     *
     * @var list<int>
     */
    public array $ignoreCodes = [404];

    /**
     * Error Views Path
     */
    public string $errorViewPath = APPPATH . 'Views/errors';

    /**
     * HIDE FROM DEBUG TRACE
     *
     * @var list<string>
     */
    public array $sensitiveDataInTrace = [];

    /**
     * LOG DEPRECATIONS
     */
    public bool $logDeprecations = true;

    /**
     * LOG LEVEL THRESHOLD FOR DEPRECATIONS
     */
    public string $deprecationLogLevel = LogLevel::WARNING;

    /**
     * DEFINE THE HANDLERS USED
     */
    public function handler(int $statusCode, Throwable $exception): ExceptionHandlerInterface
    {
        return new ExceptionHandler($this);
    }
}
