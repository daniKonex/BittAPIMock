<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Routing extends BaseConfig
{
    /**
     * Default Namespace
     */
    public $defaultNamespace = 'App\\Controllers';

    /**
     * Default Controller
     */
    public $defaultController = 'Home';

    /**
     * Default Method
     */
    public $defaultMethod = 'index';

    /**
     * Translate URI dashes
     */
    public $translateURIDashes = false;

    /**
     * Override controller and method
     */
    public $override404 = null;

    /**
     * Auto Route
     */
    public $autoRoute = false;

    /**
     * URI Protocol
     */
    public $uriProtocol = 'REQUEST_URI';

    /**
     * Multiple URI segments
     */
    public $multipleSegmentsOneParam = false;

    /**
     * Route files
     */
    public $routeFiles = [APPPATH . 'Config/Routes.php'];

    /**
     * Prioritize routes
     */
    public $prioritize = false;
}
