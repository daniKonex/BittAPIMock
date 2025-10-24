<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class UserAgents extends BaseConfig
{
    public $platforms = [
        'windows nt 10.0'  => 'Windows 10',
        'windows nt 6.3'   => 'Windows 8.1',
        'windows nt 6.2'   => 'Windows 8',
        'windows nt 6.1'   => 'Windows 7',
        'macintosh|mac os x' => 'Mac OS X',
        'linux'            => 'Linux',
    ];

    public $browsers = [
        'OPR'      => 'Opera',
        'Flock'    => 'Flock',
        'Edge'     => 'Spartan',
        'Chrome'   => 'Chrome',
        'Safari'   => 'Safari',
        'Firefox'  => 'Firefox',
        'MSIE'     => 'Internet Explorer',
    ];

    public $mobiles = [
        'iPhone'  => 'iPhone',
        'iPad'    => 'iPad',
        'Android' => 'Android',
    ];

    public $robots = [
        'googlebot'         => 'Googlebot',
        'bingbot'           => 'Bing',
        'curl'              => 'Curl',
    ];
}
