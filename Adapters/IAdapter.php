<?php
/**
 * Created by PhpStorm.
 * User: dabreu
 * Date: 2/27/20
 * Time: 3:54 p. m.
 */

namespace CSApi\Adapters;

interface IAdapter
{
    /**
     * IAdapter constructor.
     * @param mixed|null $options
     */
    public function __construct($options = null);

    /**
     * Make adapter call
     * @param string $method
     * @param string $uri
     * @param array|null $params
     * @param array|null $extraHeaders
     * @return IAdapter
     */
    public function execute(string $method, string $uri, ?array $params, ?array $extraHeaders);

    /**
     * @return string
     */
    public function getResponse(): string;

    /**
     * @return mixed
     */
    public function getInfo();
}