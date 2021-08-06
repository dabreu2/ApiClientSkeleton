<?php
/**
 * Created by PhpStorm.
 * User: dabreu
 * Date: 8/6/21
 * Time: 09:52
 */

namespace CSApi;


class ApiHubSignature
{
    /**
     * @var null|string
     */
    private $secret;

    /**
     * ApiHubSignature constructor.
     * @param string|null $secret
     */
    public function __construct(?string $secret)
    {
        $this->secret = $secret;
    }

    /**
     * @param ApiRequest $request
     * @param array $carrier
     * @return bool
     * @throws \Exception
     */
    public function injectSignatureFromRequest(ApiRequest $request, array &$carrier): bool
    {
        if (is_null($this->secret)) {
            return false;
        }

        if ($request->getMethod() == ApiRequest::METHOD_POST) {
            $content = $request->getParams();
        } else {
            $query_string = parse_url($request->getRequestUri(), PHP_URL_QUERY);
            parse_str($query_string, $content);
        }

        $carrier['x-hub-signature'] = $this->getSignature(
            json_encode($content)
        );

        return true;
    }

    /**
     * @param string $content
     * @return false|string
     */
    public function getSignature(string $content){
        if (!is_null($this->secret)){
            return hash_hmac('sha1', $content, $this->secret);
        }else {
            return false;
        }
    }

    /**
     * @param string $content
     * @param string $signature
     * @return bool
     */
    public function verify(string $content, string $signature): bool
    {
        if (!is_null($this->secret)){
            return hash_equals(
                $this->getSignature($content),
                $signature
            );
        }else {
            return false;
        }
    }
}