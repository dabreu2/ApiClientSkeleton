<?php
/**
 * Created by PhpStorm.
 * User: dabreu
 * Date: 2/28/20
 * Time: 10:33 a. m.
 */

use CSApi\Adapters\Curl;
use CSApi\Api;

include "vendor/autoload.php";

error_reporting(E_ERROR);

//$cacheAdapter = new \CSApi\Cache\Adapter\FilesystemPool('./_cache');
$mCli = class_exists('Memcached') ? new \Memcached() : new \Memcache();
$mCli->addServer('localhost', 11211);

$cacheAdapter = new \CSApi\Cache\Adapter\MemcachePool($mCli);

$api = new Api(
    'https://currency.bunkerdb.com/api/',
    [
        Api::OPT_CACHE => [
            'adapter' => $cacheAdapter,
            'ttl' => 30,
            \CSApi\Cache\CacheManager::CMO_USE_HEADERS_CACHE=>true
        ],
        Api::OPT_ADAPTER => new Curl([
            CURLOPT_TIMEOUT => 600
        ])
    ]
);

$result = (new \CSApi\ApiRequest(
    \CSApi\ApiRequest::METHOD_GET,
    "history/getrate/2021-12-12/USD/uyu",
    null,
    [
        'bapi-context: {"otype":"campaign","oid":"4822","period_id":"mnt","period_start":"2021-04-01","period_end":"2021-04-30"}'
    ]
)
)->setApi($api)->execute();
var_dump($result);
