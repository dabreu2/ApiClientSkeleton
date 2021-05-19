<?php
/**
 * Created by PhpStorm.
 * User: dabreu
 * Date: 2/28/20
 * Time: 9:30 a. m.
 */

namespace CSApi\Cache\Adapter;


use Cache\Adapter\Common\AbstractCachePool;
use Cache\Adapter\Common\PhpCacheItem;

class FilesystemPool extends AbstractCachePool
{


    /**
     * @var string
     */
    private $cache_dir;

    /**
     * FileCache constructor.
     * @param string $cache_dir
     * @param int $ttl - seconds
     */
    public function __construct(string $cache_dir)
    {
        $this->cache_dir = rtrim($cache_dir, '/');
        if(!is_dir($this->cache_dir)){
            @mkdir($this->cache_dir, 0777, true);
        }
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

        $filename = $this->cache_dir . '/' . $item->getKey();
        return file_put_contents($filename, $data);
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
        $filename = $this->cache_dir . '/' . $key;


        $data = file_exists($filename) ? @unserialize(file_get_contents($filename)) : false;
        if ($data === false) {
            return $empty;
        }

        $expirationTimestamp = $data[2] ?: null;
        if ($expirationTimestamp !== null && time() > $expirationTimestamp) {
            foreach ($data[1] as $tag) {
                $this->removeListItem($this->getTagKey($tag), $key);
            }
            $this->clearOneObjectFromCache($key);

            return $empty;
        }

        return [true, $data[0], $data[1], $expirationTimestamp];
    }

    /**
     * Clear all objects from cache.
     *
     * @return bool false if error
     */
    protected function clearAllObjectsFromCache()
    {
        foreach (glob($this->cache_dir . '/*') as $item) {
            echo $item."\n";
        }
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
        $filename = $this->cache_dir . '/' . $key;
        return unlink($filename);
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
        $filename = $this->cache_dir . '/' . $name;
        $data = @unserialize(file_get_contents($filename));
        if ($data === false) {
            $data = [];
        }
        return $data;
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
        $filename = $this->cache_dir . '/' . $name;
        return @unlink($filename);
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
        $filename = $this->cache_dir . '/' . $name;
        file_put_contents($filename, @serialize($list));
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
        $filename = $this->cache_dir . '/' . $name;
        file_put_contents($filename, @serialize($list));
    }
}