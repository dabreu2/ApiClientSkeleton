<?php
/**
 * Created by PhpStorm.
 * User: dabreu
 * Date: 2/21/20
 * Time: 5:22 p. m.
 */

namespace CSApi;


use CSApi\Interfaces\IAuthenticator;
use Exception;

class ApiRequest
{
    /** @var Api */
    private $api;

    /**
     * @var string
     */
    const METHOD_DELETE = 'DELETE';

    /**
     * @var string
     */
    const METHOD_GET = 'GET';

    /**
     * @var string
     */
    const METHOD_POST = 'POST';

    /**
     * @var string
     */
    const METHOD_PUT = 'PUT';

    /**
     * @var string
     */
    const METHOD_PATCH = 'PATCH';

    private $ALLOWED_METHODS = [
        self::METHOD_GET,
        self::METHOD_POST,
        self::METHOD_PUT,
        self::METHOD_DELETE
    ];


    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $path;

    /**
     * @var null|array
     */
    private $params;

    /**
     * @var null|array
     */
    private $headers;

    /**
     * @param string $method
     * @param string $path
     * @param array|null $params
     * @param array|null $headers
     * @throws Exception
     */
    public function __construct(string $method, string $path, ?array $params = null, ?array $headers = null)
    {
        $this
            ->setMethod($method)
            ->setPath($path)
            ->setParams($params)
            ->setHeaders($headers);
    }

    /**
     * @return Api
     * @throws Exception
     */
    private function getApi(){
        if (is_null($this->api)){
            throw new Exception("ApiClient not specified");
        }
        return $this->api;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function setApi($api){
        $this->api = $api;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return ApiRequest
     * @throws Exception
     */
    private function setMethod(string $method): ApiRequest
    {
        $method = strtoupper($method);
        if (!in_array($method, $this->ALLOWED_METHODS)) {
            throw new Exception("Invalid method");
        }
        $this->method = $method;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return ApiRequest
     */
    private function setPath(string $path): ApiRequest
    {
        $this->path = ltrim($path, '/');
        return $this;
    }

    /**
     * @return array|null
     */
    public function getParams(): ?array
    {
        return $this->params;
    }

    /**
     * @param array|null $params
     * @return ApiRequest
     */
    public function setParams(?array $params): ApiRequest
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @return array|null
     * @throws Exception
     */
    public function getHeaders(): ?array
    {
        $h = $this->headers;

        // add authentication
        if ($this->getApi()->getAuthenticator() instanceof IAuthenticator){
            $h[] = 'Authentication: ' . $this->getApi()->getAuthenticator()->authenticate();
        }

        return $h;
    }

    /**
     * @param array|null $headers
     * @return ApiRequest
     */
    public function setHeaders(?array $headers): ApiRequest
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Build request uri
     * @return string
     * @throws Exception
     */
    private function getRequestUri(){
        $base_domain = $this->getApi()->getApiBaseUri();
        $path = $this->getPath();
        return "{$base_domain}/{$path}";
    }

    private function getRequestHash(){
        $str_params = $this->getParams();
        if(is_array($str_params)){
            ksort($str_params);
        }
        $str_params = json_encode($str_params);
        return md5($this->getMethod() .
            $this->getRequestUri() .
            $str_params);
    }

    /**
     * Execute and build ApiResponse
     * @return ApiResponse
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws Exception
     */
    public function execute()
    {
        $makeCurl = true;
        if (!is_null($cm = $this->getApi()->getCacheManager())){
            $responseData = $cm->getAdapter()->get($this->getRequestHash());
            $makeCurl = is_null($responseData);
            $this->getApi()->log($makeCurl ? "Not found in cache": "Loaded from cache");
        }
        if ($makeCurl) {
            $this->getApi()->log("Request to [{$this->getMethod()}] {$this->getRequestUri()} with ".json_encode($this->getParams()));
            $responseData = $this->getApi()
                ->getAdapter()
                ->execute(
                    $this->getMethod(),
                    $this->getRequestUri(),
                    $this->getParams(),
                    $this->getHeaders()
                )
                ->getResponse();

            if (!is_null($cm)){
                $cm->getAdapter()->set($this->getRequestHash(), $responseData, $cm->getTtl());
                $this->getApi()->log("Stored data in cache");
            }
        }

        return ApiResponse::fromString($responseData, $this->getApi()->isDebug());
    }

}
