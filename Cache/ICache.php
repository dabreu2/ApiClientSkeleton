<?php
/**
 * Created by PhpStorm.
 * User: dabreu
 * Date: 2/26/20
 * Time: 3:22 p. m.
 */

namespace CFG\Cache;

use Exception;

interface ICache
{

    /**
     * Get maximum time for cache
     * @return string
     */
    public function getTTL(): ?string;

    /**
     * Set cached data
     * @param int $value
     * @return self
     */
    public function setTTL(int $value): self;

    /**
     * Get cached data
     * @param string $hash
     * @throws Exception - On not found
     * @return mixed
     */
    public function get(string $hash);

    /**
     * Set cached data
     * @param string $hash
     * @param mixed $value
     * @return ICache
     */
    public function set(string $hash, $value): self;
}