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
use CSApi\Interfaces\IAuthenticator;
use Exception;
use Monolog\Logger;
use Psr\SimpleCache\CacheInterface;

class Api
{
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
     * @var IAuthenticator
     */
    private $authenticator;

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

        if (isset($options['debug'])){
            $this->debug = $options['debug'];
        }

        if (isset($options['logger'])){
            $this->logger = $options['logger'];
        }

        if (isset($options['authenticator'])){
            $this->authenticator = $options['authenticator'];
        }

        if (isset($options['cache']) && !empty($options['cache'])){
            // set cache adapter
            if (!$options['cache']['adapter'] instanceof CacheInterface){
                throw new Exception("Cache handler must implements CacheInterface interface");
            }else{
                $this->cacheManager = new CacheManager($options['cache']['adapter']);
            }

            // set default ttl
            if (!empty($options['cache']['ttl'])){
                $this->cacheManager->setTtl((int) $options['cache']['ttl']);
            }
        }

        if (isset($options['adapter']) && !empty($options['adapter'])){
            if (!$options['adapter'] instanceof IAdapter){
                throw new Exception("Adapter handler must implements IAdapter interface");
            }else{
                $this->clientAdapter = $options['adapter'];
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
     * @return IAuthenticator|null
     */
    public function getAuthenticator(){
        return $this->authenticator;
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