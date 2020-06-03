<?php
/**
 * Created by PhpStorm.
 * User: dabreu
 * Date: 4/20/20
 * Time: 5:06 p. m.
 */

namespace CSApi\Interfaces;


interface IAuthorization
{
    /**
     * Return authorization string to use in ApiRequest
     * @throws \Exception
     * @return string
     */
    public function authorizate(): string;
}