<?php
/**
 * Created by PhpStorm.
 * User: dabreu
 * Date: 2/27/20
 * Time: 12:01 p. m.
 */

namespace CFG\Cache;


use Exception;
use Memcache;
use Memcached;

class MemcacheCache implements ICache
{

    protected $ttl;

    /**
     * @var string
     */
    private $cache_dir;
    /**
     * @var string
     */
    private $host;
    /**
     * @var int
     */
    private $port;

    /**
     * MemCache constructor.
     * @param string $host
     * @param int $port
     * @param int $ttl - seconds
     */
    public function __construct(string $host='localhost', int $port=11211, $ttl = 300)
    {
        $this->host = $host;
        $this->port = $port;
        $this->ttl = $ttl;
    }

    /**
     * Get maximum time for cache
     * @return string
     */
    public function getTTL(): ?string
    {
        return $this->ttl;
    }

    /**
     * Set cached data
     * @param int $value
     * @return self
     */
    public function setTTL(int $value): ICache
    {
        $this->ttl = $value;
        return $this;
    }

    /**
     * @return Memcache|Memcached
     */
    private function getClient(){
        $client = (class_exists('Memcached') ? new Memcached() : new Memcache());
        $client->addServer($this->host, $this->port);
        return $client;
    }

    /**
     * Set cached data
     * @param string $hash
     * @param mixed $value
     * @return ICache
     */
    public function set(string $hash, $value): ICache
    {
        $this
            ->getClient()
            ->set($hash, json_encode($value), $this->ttl);
        return $this;
    }

    /**
     * Get cached data
     * @param string $hash
     * @return mixed
     * @throws Exception - On not found
     */
    public function get(string $hash)
    {
        if (($data = $this->getClient()->get($hash)) === false) {
            throw new Exception('Not found', 404);
        }
        return json_decode($data, true);
    }
}