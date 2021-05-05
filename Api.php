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
use CSApi\Interfaces\IAuthorization;
use Exception;
use Monolog\Logger;
use Psr\SimpleCache\CacheInterface;

class Api
{
    const OPT_AUTHORIZATION = 'authorization';
    const OPT_DEBUG = 'debug';
    const OPT_LOGGER = 'logger';
    const OPT_CACHE = 'cache';
    const OPT_ADAPTER = 'adapter';

    /**
     * @var string
     */
    private $api_base_uri;

    /**
     * @var bool
     */
    private $debug=false;

    /**
     * @var Logger|Callable
     */
    private $logger;

    /**
     * @var IAuthorization
     */
    private $authorization;

    /**
     * @var CacheManager
     */
    private $cacheManager=null;

    /**
     * @var IAdapter
     */
    private $clientAdapter;

    /**
     * @param string $api_base_uri
     * @param array $options
     * @throws Exception
     */
    public function __construct(string $api_base_uri, array $options = [])
    {
        $this->api_base_uri = rtrim($api_base_uri, '/');

        if (empty($this->api_base_uri)){
            throw new Exception("API base domain missing");
        }

        /**
         * load options
         */

        if (isset($options[self::OPT_DEBUG])){
            $this->debug = $options[self::OPT_DEBUG];
        }

        if (isset($options[self::OPT_LOGGER])){
            $this->logger = $options[self::OPT_LOGGER];
        }

        if (isset($options[self::OPT_AUTHORIZATION])){
            $this->authorization = $options[self::OPT_AUTHORIZATION];
        }

        if (isset($options[self::OPT_CACHE]) && !empty($options[self::OPT_CACHE])){
            // set cache adapter
            if (!$options[self::OPT_CACHE]['adapter'] instanceof CacheInterface){
                throw new Exception("Cache handler must implements CacheInterface interface");
            }else{
                $this->cacheManager = new CacheManager($options[self::OPT_CACHE]['adapter']);
                //remove adapter to not added plus in options
                unset($options[self::OPT_CACHE]['adapter']);
            }

            // set user options
            $this->cacheManager->setOptions($options[self::OPT_CACHE]);
        }

        if (isset($options[self::OPT_ADAPTER]) && !empty($options[self::OPT_ADAPTER])){
            if (!$options[self::OPT_ADAPTER] instanceof IAdapter){
                throw new Exception("Adapter handler must implements IAdapter interface");
            }else{
                $this->clientAdapter = $options[self::OPT_ADAPTER];
            }
        }else{
            $this->clientAdapter = new Curl();
        }
    }

    /**
     * @return Logger|Callable
     */
    public function getLogger(){
        return $this->logger;
    }

    /**
     * @return IAuthorization|null
     */
    public function getAuthorization(){
        return $this->authorization;
    }

    /**
     * @param $message
     * @param int $level
     */
    public function log($message, $level = Logger::INFO){
        if ($this->logger instanceof Logger){
            $method = strtolower(Logger::getLevelName($level));
            $this->logger->$method($message);
        }elseif(is_callable($this->logger)){
            $l = $this->logger;
            $l($message, $level);
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