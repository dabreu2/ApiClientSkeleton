<?php
/**
 * Created by PhpStorm.
 * User: dabreu
 * Date: 2/28/20
 * Time: 11:10 a. m.
 */

namespace CSApi\Cache\Adapter;


use Cache\Adapter\Common\AbstractCachePool;
use Cache\Adapter\Common\PhpCacheItem;

class RedisPool extends AbstractCachePool
{

    /**
     * @var \Redis
     */
    private $client;

    /**
     * RedisPool constructor.
     * @param $client
     */
    public function __construct($client)
    {
        $this->client = $client;
    }


    /**
     * @param PhpCacheItem $item
     * @param int|null $ttl seconds from now
     *
     * @return bool true if saved
     */
    protected function storeItemInCache(PhpCacheItem $item, $ttl)
    {
        $data = serialize(
            [
                $item->get(),
                $item->getTags(),
                $item->getExpirationTimestamp(),
            ]
        );
        return $this->client->set($item->getKey(), $data, $ttl);
    }

    /**
     * Fetch an object from the cache implementation.
     *
     * If it is a cache miss, it MUST return [false, null, [], null]
     *
     * @param string $key
     *
     * @return array with [isHit, value, tags[], expirationTimestamp]
     */
    protected function fetchObjectFromCache($key)
    {
        $empty = [false, null, [], null];

        $data = @unserialize($this->client->get($key));
        if ($data === false) {
            return $empty;
        }
        $expirationTimestamp = $data[2] ?: null;
        return [true, $data[0], $data[1], $expirationTimestamp];
    }

    /**
     * Clear all objects from cache.
     *
     * @return bool false if error
     */
    protected function clearAllObjectsFromCache()
    {
        return true;
    }

    /**
     * Remove one object from cache.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function clearOneObjectFromCache($key)
    {
        return $this->client->del($key);
    }

    /**
     * Get an array with all the values in the list named $name.
     *
     * @param string $name
     *
     * @return array
     */
    protected function getList($name)
    {
        $list = $this->fetchObjectFromCache($name);
        if (!is_array($list)){
            $list = [];
        }
        return $list;
    }

    /**
     * Remove the list.
     *
     * @param string $name
     *
     * @return bool
     */
    protected function removeList($name)
    {
        return $this->clearOneObjectFromCache($name);
    }

    /**
     * Add a item key on a list named $name.
     *
     * @param string $name
     * @param string $key
     */
    protected function appendListItem($name, $key)
    {
        $list = $this->getList($name);
        $list[] = $key;
        $this->client->set($name, @serialize($list));
    }

    /**
     * Remove an item from the list.
     *
     * @param string $name
     * @param string $key
     */
    protected function removeListItem($name, $key)
    {
        $list = $this->getList($name);
        $list = array_values(array_filter($list, function($v) use ($key){
            return $v != $key;
        }));
        $this->client->set($name, @serialize($list));
    }
}