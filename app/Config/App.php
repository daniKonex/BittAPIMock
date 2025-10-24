<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    public $baseURL = 'http://localhost:8090/';
    public $debug = false;
    public $indexPage = '';
    public $uriProtocol = 'REQUEST_URI';
    public $defaultLocale = 'es';
    public $negotiateLocale = false;
    public $supportedLocales = ['es', 'en'];
    public $appTimezone = 'Europe/Madrid';
    public $charset = 'UTF-8';
    public $forceGlobalSecureRequests = false;
    public $proxyIPs = [];
    public $CSRFProtection = false;
    public $CSRFTokenName = 'csrf_token_name';
    public $CSRFHeaderName = 'X-CSRF-TOKEN';
    public $CSRFCookieName = 'csrf_cookie_name';
    public $CSRFExpire = 7200;
    public $CSRFRegenerate = true;
    public $CSRFRedirect = true;
    public $CSRFSameSite = 'Lax';
    public $cookiePrefix = '';
    public $cookieDomain = '';
    public $cookiePath = '/';
    public $cookieSecure = false;
    public $cookieHTTPOnly = true;
    public $cookieSameSite = 'Lax';
    public $allowedHostnames = [];
    public $CSPEnabled = false;
}
