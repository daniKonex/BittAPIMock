<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Log\Handlers\FileHandler;

class Logger extends BaseConfig
{
    /**
     * Error Logging Threshold
     */
    public $threshold = (ENVIRONMENT === 'production') ? 4 : 9;

    /**
     * Date Format for Logs
     */
    public string $dateFormat = 'Y-m-d H:i:s';

    /**
     * Log Handlers
     */
    public array $handlers = [
        FileHandler::class => [
            'handles' => [
                'critical',
                'alert',
                'emergency',
                'debug',
                'error',
                'info',
                'notice',
                'warning',
            ],
            'fileExtension' => '',
            'filePermissions' => 0644,
            'path' => '',
        ],
    ];
}
