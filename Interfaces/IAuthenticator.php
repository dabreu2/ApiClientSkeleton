<?php
/**
 * Created by PhpStorm.
 * User: dabreu
 * Date: 4/20/20
 * Time: 5:06 p. m.
 */

namespace CSApi\Interfaces;


interface IAuthenticator
{
    /**
     * Return Authenticate string to use in ApiRequest
     * @throws \Exception
     * @return string
     */
    public function authenticate(): string;
}