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
    /**
     * Only cache this methods (array: default [GET])
     */
    const CMO_HTTP_METHODS = 'http_methods';
    /**
     * Cache responses when http code match with expression (string: default '2\d\d')
     */
    const CMO_HTTP_CODES = 'http_codes';
    /**
     * Include headers in cache key (array: default [])
     */
    const CMO_HTTP_HEADERS = 'http_headers';

    /**
     * @var CacheInterface
     */
    private $adapter;

    private $default_options = [
        self::CMO_TTL => 300,
        self::CMO_HTTP_METHODS => [ApiRequest::METHOD_GET],
        self::CMO_HTTP_CODES => '2\d\d',
        self::CMO_HTTP_HEADERS => []
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
        if (is_array($this->options[self::CMO_HTTP_HEADERS]) &&
            count($this->options[self::CMO_HTTP_HEADERS]) > 0) {
            // build patterns
            $pattern = $this->options[self::CMO_HTTP_HEADERS];
            $pattern = array_map('preg_quote', $pattern);
            $pattern = '/^\s?('.implode('|',$pattern).')\s?\:/i';

            // filter headers by pattern
            $r_headers = array_filter($request->getHeaders(), function($h) use($pattern){
               return preg_match($pattern, $h);
            });

            $headers = implode("|",$r_headers);
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