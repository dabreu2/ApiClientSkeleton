<?php
/**
 * Created by PhpStorm.
 * User: dabreu
 * Date: 2/27/20
 * Time: 3:55 p. m.
 */

namespace CFG\Adapters;

class Curl implements \IAdapter
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
     * Make adapter call
     * @param string $method
     * @param string $uri
     * @param array|null $params
     * @param array|null $extraHeaders
     * @return \IAdapter
     */
    public function execute(string $method, string $uri, ?array $params, array $extraHeaders = [])
    {
        $curl = curl_init();

        $headers = [
            "Content-Type: application/json"
        ];

        if (is_array($extraHeaders)){
            $headers = array_merge($headers, $extraHeaders);
        }

        $options = [
            CURLOPT_URL => $uri,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers
        ];

        if (!empty($params)){
            $options[CURLOPT_POSTFIELDS] = json_encode($params);
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        return $this
            ->setResponse($response)
            ->setInfo($info);
    }

    /**
     * @return string|null
     */
    public function getResponse(): string
    {
        return $this->response;
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