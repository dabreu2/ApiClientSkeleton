<?php
/**
 * Created by PhpStorm.
 * User: dabreu
 * Date: 2/21/20
 * Time: 5:13 p. m.
 */

namespace CSApi;

use CSApi\Adapters\Curl;
use CSApi\Adapters\IAdapter;
use CSApi\Cache\CacheManager;
use Exception;
use Psr\SimpleCache\CacheInterface;

class Api
{
    /**
     * @var Api
     */
    private static $_instance = null;

    /**
     * @var string
     */
    private $client_id;

    /**
     * @var string
     */
    private $service_id;

    /**
     * @var string
     */
    private $api_base_uri;

    /**
     * @var bool
     */
    private $debug=false;

    /**
     * @var CacheManager
     */
    private $cacheManager=null;


    /**
     * @var IAdapter
     */
    private $clientAdapter;

    /**
     * @param string $client_id
     * @param string $service_id
     * @param string $api_base_uri
     * @param array $options
     * @throws Exception
     */
    public static function init(string $client_id, string $service_id, string $api_base_uri, array $options = [])
    {
        $inst = self::getInstance();
        $inst->client_id = $client_id;
        $inst->service_id = $service_id;
        $inst->api_base_uri = rtrim($api_base_uri, '/');

        if (empty($inst->api_base_uri)){
            throw new Exception("API base domain missing");
        }

        /**
         * load options
         */

        if (isset($options['debug'])){
            $inst->debug = $options['debug'];
        }

        if (isset($options['cache']) && !empty($options['cache'])){
            // set cache adapter
            if (!$options['cache']['adapter'] instanceof CacheInterface){
                throw new Exception("Cache handler must implements CacheInterface interface");
            }else{
                $inst->cacheManager = new CacheManager($options['cache']['adapter']);
            }

            // set default ttl
            if (!empty($options['cache']['ttl'])){
                $inst->cacheManager->setTtl((int) $options['cache']['ttl']);
            }
        }

        if (isset($options['adapter']) && !empty($options['adapter'])){
            if (!$options['adapter'] instanceof IAdapter){
                throw new Exception("Adapter handler must implements IAdapter interface");
            }else{
                $inst->clientAdapter = $options['adapter'];
            }
        }else{
            $inst->clientAdapter = new Curl();
        }
    }

    /**
     * @return Api
     */
    public static function getInstance(){
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }


    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->client_id;
    }

    /**
     * @return string
     */
    public function getServiceId(): string
    {
        return $this->service_id;
    }

    /**
     * @return string
     */
    public function getApiBaseUri()
    {
        return $this->api_base_uri;
    }

    /**
     * @return CacheManager|null
     */
    public function getCacheManager(): ?CacheManager
    {
        return $this->cacheManager;
    }

    /**
     * @return IAdapter
     */
    public function getAdapter(){
        return $this->clientAdapter;
    }
}