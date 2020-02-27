<?php
/**
 * Created by PhpStorm.
 * User: dabreu
 * Date: 2/26/20
 * Time: 3:22 p. m.
 */

namespace CFG\Cache;

use Exception;

class FileCache implements ICache
{

    protected $ttl;

    /**
     * @var string
     */
    private $cache_dir;

    /**
     * FileCache constructor.
     * @param string $cache_dir
     * @param int $ttl - seconds
     */
    public function __construct(string $cache_dir, $ttl = 300)
    {
        $this->cache_dir = rtrim($cache_dir, '/');
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
     * Set cached data
     * @param string $hash
     * @param mixed $value
     * @return ICache
     */
    public function set(string $hash, $value): ICache
    {
        $filename = $this->cache_dir . '/' . $hash;
        file_put_contents($filename, json_encode($value));
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
        $filename = $this->cache_dir . '/' . $hash;
        if (!file_exists($filename)){
            throw new Exception('Not found', 404);
        }else{
            $ft = filemtime($filename);
            if ($ft === false || ((time() - $ft) > $this->ttl)){
                throw new Exception('Expired', 410);
            }else{
                return json_decode(file_get_contents($filename), true);
            }
        }
    }
}