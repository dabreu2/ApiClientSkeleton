<?php
/**
 * Created by PhpStorm.
 * User: dabreu
 * Date: 2/21/20
 * Time: 5:15 p. m.
 */

namespace CFG\Objects;

use CFG\Api;
use CFG\ApiRequest;
use CFG\ApiResponse;

class Test
{
    /**
     * @return ApiResponse
     * @throws \Exception
     */
    public function get()
    {
        $request = new ApiRequest(
            ApiRequest::METHOD_GET,
            Api::getInstance()->getClientId() . "/" . Api::getInstance()->getServiceId() . "/test"
        );
        return $request->execute();
    }
}