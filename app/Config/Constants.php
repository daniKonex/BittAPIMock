<?php

/*
 | --------------------------------------------------------------------
 | Environment
 | --------------------------------------------------------------------
 */
if (! defined('ENVIRONMENT')) {
    define('ENVIRONMENT', getenv('CI_ENVIRONMENT') ?: 'production');
}

/*
 | --------------------------------------------------------------------
 | App Namespace
 | --------------------------------------------------------------------
 */
defined('APP_NAMESPACE') || define('APP_NAMESPACE', 'App');

/*
 | --------------------------------------------------------------------
 | Composer Path
 | --------------------------------------------------------------------
 */
defined('COMPOSER_PATH') || define('COMPOSER_PATH', ROOTPATH . 'vendor/autoload.php');

/*
 | --------------------------------------------------------------------
 | Writable Directory Path
 | --------------------------------------------------------------------
 */
defined('WRITEPATH') || define('WRITEPATH', ROOTPATH . 'writable' . DIRECTORY_SEPARATOR);

/*
 | --------------------------------------------------------------------
 | Timing Constants
 | --------------------------------------------------------------------
 */
defined('SECOND') || define('SECOND', 1);
defined('MINUTE') || define('MINUTE', 60);
defined('HOUR')   || define('HOUR', 3600);
defined('DAY')    || define('DAY', 86400);
defined('WEEK')   || define('WEEK', 604800);
defined('MONTH')  || define('MONTH', 2592000);
defined('YEAR')   || define('YEAR', 31536000);
defined('DECADE') || define('DECADE', 315360000);

/*
 | --------------------------------------------------------------------
 | Exit Status Codes
 | --------------------------------------------------------------------
 */
defined('EXIT_SUCCESS')        || define('EXIT_SUCCESS', 0);
defined('EXIT_ERROR')          || define('EXIT_ERROR', 1);
defined('EXIT_CONFIG')         || define('EXIT_CONFIG', 3);
defined('EXIT_UNKNOWN_FILE')   || define('EXIT_UNKNOWN_FILE', 4);
defined('EXIT_UNKNOWN_CLASS')  || define('EXIT_UNKNOWN_CLASS', 5);
defined('EXIT_UNKNOWN_METHOD') || define('EXIT_UNKNOWN_METHOD', 6);
defined('EXIT_USER_INPUT')     || define('EXIT_USER_INPUT', 7);
defined('EXIT_DATABASE')       || define('EXIT_DATABASE', 8);
defined('EXIT__AUTO_MIN')      || define('EXIT__AUTO_MIN', 9);
defined('EXIT__AUTO_MAX')      || define('EXIT__AUTO_MAX', 125);

/**
 * @deprecated Use \CodeIgniter\Events\Events::PRIORITY_LOW instead.
 */
define('EVENT_PRIORITY_LOW', 200);

/**
 * @deprecated Use \CodeIgniter\Events\Events::PRIORITY_NORMAL instead.
 */
define('EVENT_PRIORITY_NORMAL', 100);

/**
 * @deprecated Use \CodeIgniter\Events\Events::PRIORITY_HIGH instead.
 */
define('EVENT_PRIORITY_HIGH', 10);
