<?php
/**
 * Created by PhpStorm.
 * User: dabreu
 * Date: 7/23/21
 * Time: 16:34
 */

namespace CSApi\OpenTracing;


interface ITracing
{
    /**
     * @return $this
     */
    public function start(): self;

    /**
     * @param string $key
     * @param string|bool|int|float $value
     * @return $this
     */
    public function setTag(string $key, $value): self;

    /**
     * @param array $data
     * @param null $timestamp
     * @return $this
     */
    public function log(array $data = [], $timestamp = null): self;

    /**
     * @return $this
     */
    public function close(): self;
}