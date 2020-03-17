<?php
/**
 * Created by PhpStorm.
 * User: dabreu
 * Date: 2/21/20
 * Time: 5:15 p. m.
 */

namespace CSApi\Objects;

use CSApi\Api;
use CSApi\ApiRequest;
use CSApi\ApiResponse;

class Test
{
    /**
     * @return ApiResponse
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Exception
     */
    public function get()
    {
        $request = new ApiRequest(
            ApiRequest::METHOD_GET,
            'instances/' . Api::getInstance()->getClientId() . "/services/" . Api::getInstance()->getServiceId()
        );
        return $request->execute();
    }
}