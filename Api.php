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
use Monolog\Logger;
use Psr\SimpleCache\CacheInterface;

class Api
{
    /**
     * @var Api
     */
    private static $_instance = null;

    /**
     * @var $array
     */
    private $context;

    /**
     * @var string
     */
    private $api_base_uri;

    /**
     * @var bool
     */
    private $debug=false;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var CacheManager
     */
    private $cacheManager=null;

    /**
     * @var IAdapter
     */
    private $clientAdapter;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @param string $api_base_uri
     * @param array $context
     * @param array $options
     * @return Api
     * @throws Exception
     */
    public static function init(string $api_base_uri, array $context = [], array $options = [])
    {
        try {
            $inst = self::getInstance();
        }catch(Exception $e){
            $inst = self::$_instance;
        }
        $inst->api_base_uri = rtrim($api_base_uri, '/');
        $inst->context = $context;

        if (empty($inst->api_base_uri)){
            throw new Exception("API base domain missing");
        }

        /**
         * load options
         */

        if (isset($options['debug'])){
            $inst->debug = $options['debug'];
        }

        if (isset($options['logger'])){
            $inst->logger = $options['logger'];
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

        $inst->initialized = true;

        return $inst;
    }

    /**
     * @return Api
     * @throws Exception
     */
    public static function getInstance(){
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        if (!self::$_instance->initialized){
            throw new Exception("Api not initialized");
        }
        return self::$_instance;
    }

    /**
     * @return Logger
     */
    public function getLogger(): ?Logger{
        return $this->logger;
    }

    /**
     * @param $message
     * @param int $level
     */
    public function log($message, $level = Logger::INFO){
        if ($this->logger instanceof Logger){
            $method = strtolower(Logger::getLevelName($level));
            $this->logger->$method($message);
        }
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }


    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
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