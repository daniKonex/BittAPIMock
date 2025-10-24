<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Toolbar extends BaseConfig
{
    /**
     * Toolbar collectors
     *
     * @var list<class-string>
     */
    public array $collectors = [];

    /**
     * Collect Var Data
     */
    public bool $collectVarData = true;

    /**
     * Max History
     */
    public int $maxHistory = 20;

    /**
     * Toolbar Views Path
     */
    public string $viewsPath = SYSTEMPATH . 'Debug/Toolbar/Views/';

    /**
     * Max Queries
     */
    public int $maxQueries = 100;

    /**
     * Watch Directory
     */
    public $watchedDirectories = [APPPATH];

    /**
     * Watch File Pattern
     */
    public string $watchedFileExtensions = 'php, css, js, env';
}
