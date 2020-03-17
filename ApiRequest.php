<?php
/**
 * Created by PhpStorm.
 * User: dabreu
 * Date: 2/21/20
 * Time: 5:22 p. m.
 */

namespace CSApi;


use Exception;

class ApiRequest
{
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
     * @param string $method
     * @param string $path
     * @throws Exception
     */
    public function __construct(string $method, string $path)
    {
        $this
            ->setMethod($method)
            ->setPath($path);
    }

    /**
     * @return Api
     */
    private function getApi(){
        return Api::getInstance();
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
        $this->path = $path;
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
    private function setParams(?array $params): ApiRequest
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Build request uri
     * @return string
     */
    private function getRequestUri(){
        $base_domain = $this->getApi()->getApiBaseUri();
        $path = $this->getPath();
        return "{$base_domain}/{$path}";
    }

    private function getRequestHash(){
        $str_params = $this->getParams();
        ksort($str_params);
        $str_params = json_encode($str_params);
        return md5($this->getMethod() .
            $this->getRequestUri() .
            $str_params);
    }

    /**
     * Execute and build ApiResponse
     * @return ApiResponse
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function execute()
    {
        $makeCurl = true;
        if (!is_null($cm = Api::getInstance()->getCacheManager())){
            $responseData = $cm->getAdapter()->get($this->getRequestHash());
            $makeCurl = is_null($responseData);
        }
        if ($makeCurl) {
            $responseData = $this->getApi()
                ->getAdapter()
                ->execute(
                    $this->getMethod(),
                    $this->getRequestUri(),
                    $this->getParams()
                )
                ->getResponse();

            if (!is_null($cm)){
                $cm->getAdapter()->set($this->getRequestHash(), $responseData, $cm->getTtl());
            }
        }

        return ApiResponse::fromString($responseData);
    }

}