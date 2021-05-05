<?php
/**
 * Created by PhpStorm.
 * User: dabreu
 * Date: 2/28/20
 * Time: 10:43 a. m.
 */

namespace CSApi\Cache;


use CSApi\ApiRequest;
use Psr\SimpleCache\CacheInterface;

class CacheManager
{
    const CMO_TTL = 'ttl';
    const CMO_HTTP_METHODS = 'http_methods';
    const CMO_HTTP_CODES = 'http_codes';
    const CMO_CACHE_HEADER_RULES = 'cache_header_rules';

    /**
     * @var CacheInterface
     */
    private $adapter;

    private $default_options = [
        self::CMO_TTL => 300,
        self::CMO_HTTP_METHODS => [ApiRequest::METHOD_GET],
        self::CMO_HTTP_CODES => '2\d\d',
        self::CMO_CACHE_HEADER_RULES => []
    ];

    /** @var array */
    private $options = [];

    /**
     * CacheManager constructor.
     * @param CacheInterface $adapter
     */
    public function __construct(CacheInterface $adapter)
    {
        $this->adapter = $adapter;
        $this->options = $this->default_options;
    }

    /**
     * @return CacheInterface
     */
    public function getAdapter(): CacheInterface
    {
        return $this->adapter;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return CacheManager
     */
    public function setOptions(array $options): CacheManager
    {
        $ret = $this->default_options;
        foreach ($ret as $k => $v) {
            if (isset($options[$k])) {
                $ret[$k] = $options[$k];
            }
        }
        $this->options = $ret;
        return $this;
    }

    /**
     * @param CacheInterface $adapter
     * @return CacheManager
     */
    public function setAdapter(CacheInterface $adapter): CacheManager
    {
        $this->adapter = $adapter;
        return $this;
    }


    private function createHash(ApiRequest $request)
    {
        $str_params = $request->getParams();
        if (is_array($str_params)) {
            ksort($str_params);
        }
        $str_params = json_encode($str_params);
        $headers = '';
        if ($this->options[self::CMO_CACHE_HEADER_RULES]) {
            $aux = [];
            foreach ($this->options[self::CMO_CACHE_HEADER_RULES] as $header) {
                $aux[] = json_encode($request->getHeaders()[$headers]);
            }
            $headers = implode("|", $aux);
        }

        return md5($request->getMethod() .
            $request->getRequestUri() .
            $headers .
            $str_params);
    }

    public function get(ApiRequest $request, $default_value = null)
    {
        return $this->getAdapter()->get($this->createHash($request), $default_value);
    }

    public function set(ApiRequest $request, $data)
    {
        return $this->getAdapter()->set($this->createHash($request), $data, $this->getOptions()[CacheManager::CMO_TTL]);
    }

    /**
     * @param ApiRequest $request
     * @param string $responseData
     * @return bool
     * @throws \Exception
     */
    public function mustCacheResponse(ApiRequest $request, string $responseData)
    {
        // check method allowed to cache
        if (!in_array(strtoupper($request->getMethod()), $this->getOptions()[self::CMO_HTTP_METHODS])) {
            return false;
        }

        // check http_code allowed to cache
        $data = json_decode($responseData, true);
        if (!preg_match('/^' . $this->getOptions()[self::CMO_HTTP_CODES] . '$/', $data['statusCode'])) {
            return false;
        }

        return true;
    }
}