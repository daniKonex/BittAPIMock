<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class ContentSecurityPolicy extends BaseConfig
{
    public $reportOnly = false;
    public $defaultSrc = null;
    public $scriptSrc = 'self';
    public $styleSrc = 'self';
    public $imgSrc = 'self';
    public $baseURI = null;
    public $childSrc = null;
    public $connectSrc = 'self';
    public $fontSrc = null;
    public $formAction = null;
    public $frameAncestors = null;
    public $frameSrc = null;
    public $mediaSrc = null;
    public $objectSrc = 'self';
    public $manifestSrc = null;
    public $pluginTypes = null;
    public $sandbox = false;
    public $upgradeInsecureRequests = false;
    public $autoNonce = true;
}
