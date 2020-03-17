<?php
/**
 * Created by PhpStorm.
 * User: dabreu
 * Date: 2/28/20
 * Time: 10:33 a. m.
 */

include "vendor/autoload.php";

error_reporting(E_ERROR);

//$cacheAdapter = new \CSApi\Cache\Adapter\FilesystemPool('./_cache');
$mCli = class_exists('Memcached') ? new \Memcached() : new \Memcache();
$mCli->addServer('localhost', 11211);

$cacheAdapter = new \CSApi\Cache\Adapter\MemcachePool($mCli);

\CSApi\Api::init(
    'app1',
    'service1',
    'http://domain.com/api/v1',
    [
        'debug' => true,
        'cache' => [
            'adapter' => $cacheAdapter,
            'ttl' => 20
        ]
    ]
);


// Object driven request
$testInfo = (new \CSApi\Objects\Test())
    ->get();

print_r($testInfo->getData());