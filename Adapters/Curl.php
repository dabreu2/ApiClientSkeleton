<?php
/**
 * Created by PhpStorm.
 * User: dabreu
 * Date: 2/27/20
 * Time: 3:55 p. m.
 */

namespace CSApi\Adapters;

class Curl implements IAdapter
{
    /**
     * @var string
     */
    protected $response;

    /**
     * @var mixed
     */
    protected $info;

    /**
     * @var int
     */
    protected $errorCode;

    /**
     * @var string
     */
    protected $errorMsg;

    /** @var array|null */
    private $options;

    /**
     * Curl constructor.
     * @param array|null $options
     */
    function __construct($options = null)
    {
        $this->options = $options;
    }

    /**
     * @param $uri
     * @param $method
     * @param $extraHeaders
     * @param $params
     * @return array
     */
    private function getOptions($uri, $method, $extraHeaders, $params){
        $headers = [
            "Content-Type: application/json"
        ];

        if (is_array($extraHeaders) && !empty($extraHeaders)){
            $headers = $this->mergeHeaders($headers, $extraHeaders);
        }

        $options = [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers
        ];

        if (is_array($this->options) && !empty($this->options)) {
            $options = array_replace($options, $this->options);
        }

        if (!empty($params)) {
            if ($method == 'GET') {
                $uri .= (strpos($uri, '?') === false ? '?' : '&') . http_build_query($params);
            } else {
                $options[CURLOPT_POSTFIELDS] = json_encode($params);
            }
        }
        $options[CURLOPT_URL] = $uri;


        return $options;
    }

    /**
     * Make adapter call
     * @param string $method
     * @param string $uri
     * @param array|null $params
     * @param array|null $extraHeaders
     * @return IAdapter
     */
    public function execute(string $method, string $uri, ?array $params, ?array $extraHeaders)
    {
        $curl = curl_init();

        $options = $this->getOptions(
            $uri,
            $method,
            $extraHeaders,
            $params
        );

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $error = curl_errno($curl);
        $errorMsg = curl_error($curl);
        curl_close($curl);

        return $this
            ->setResponse($response)
            ->setInfo($info)
            ->setErrorCode($error)
            ->setErrorMsg($errorMsg);
    }

    /**
     * Get unique headers
     * @param $headers
     * @param $extra
     * @return array
     */
    private function mergeHeaders($headers, $extra){
        $ret = [];
        foreach ([$headers, $extra] as $entries) {
            foreach ($entries as $k => $header) {
                if (is_string($k) && !is_numeric($k)) {
                    $k = strtolower($k);
                    $header = "$k: $header";
                } else {
                    $k = strtolower(trim(substr($header, 0, strpos($header, ':'))));
                }
                $ret[$k] = $header;
            }
        }
        return array_values($ret);
    }

    /**
     * @param string $error
     * @return $this
     */
    private function setErrorMsg(?string $error): Curl{
        $this->errorMsg = $error;
        return $this;
    }

    /**
     * @param int $error
     * @return $this
     */
    private function setErrorCode(int $error): Curl{
        $this->errorCode = $error;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getResponse(): string
    {
        $ret = [
            'statusCode' => $this->getInfo()['http_code'],
            'response' => $this->response,
            'error' => $this->errorCode !== 0 ? "({$this->errorCode}) $this->errorMsg" : false
        ];
        return json_encode($ret);
    }

    /**
     * @param string|null $response
     * @return Curl
     */
    private function setResponse(?string $response): Curl
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @param mixed|null $info
     * @return Curl
     */
    private function setInfo($info): Curl
    {
        $this->info = $info;
        return $this;
    }
}