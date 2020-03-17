<?php
/**
 * Created by PhpStorm.
 * User: dabreu
 * Date: 2/28/20
 * Time: 10:43 a. m.
 */

namespace CSApi\Cache;


use Psr\SimpleCache\CacheInterface;

class CacheManager
{
    /**
     * @var CacheInterface
     */
    private $adapter;

    /**
     * @var integer
     */
    private $ttl;

    /**
     * CacheManager constructor.
     * @param CacheInterface $adapter
     * @param int $ttl
     */
    public function __construct(CacheInterface $adapter, int $ttl=300)
    {
        $this->adapter = $adapter;
        $this->ttl = $ttl;
    }

    /**
     * @return CacheInterface
     */
    public function getAdapter(): CacheInterface
    {
        return $this->adapter;
    }

    /**
     * @return int
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }

    /**
     * @param CacheInterface $adapter
     * @return CacheManager
     */
    public function setAdapter(CacheInterface $adapter): CacheManager
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * @param int $ttl
     * @return CacheManager
     */
    public function setTtl(int $ttl): CacheManager
    {
        $this->ttl = $ttl;
        return $this;
    }
}